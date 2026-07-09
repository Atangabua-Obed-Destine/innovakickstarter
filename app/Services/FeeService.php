<?php

namespace App\Services;

use App\Models\Fee;
use App\Models\FeeInstallment;
use App\Models\FeePayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Fee Service
 *
 * Handles all business logic for fee assignment, payments,
 * receipt generation, and status management.
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class FeeService
{
    // ==========================================
    // FEE ASSIGNMENT
    // ==========================================

    /**
     * Assign a new fee to a fellow.
     *
     * @param array $data Validated fee data
     * @param User $admin The admin assigning the fee
     * @return Fee
     */
    public function assignFee(array $data, User $admin): Fee
    {
        return DB::transaction(function () use ($data, $admin) {
            $fee = Fee::create([
                'reference'           => $this->generateFeeReference(),
                'fellow_id'           => $data['fellow_id'],
                'billable_type'       => $data['billable_type'] ?? null,
                'billable_id'         => $data['billable_id'] ?? null,
                'title'               => $data['title'],
                'description'         => $data['description'] ?? null,
                'amount_total'        => $data['amount_total'],
                'amount_paid'         => 0,
                'currency'            => 'XAF',
                'plan_type'           => $data['plan_type'] ?? Fee::PLAN_ONE_TIME,
                'installments_count'  => $data['installments_count'] ?? null,
                'installment_cadence' => $data['installment_cadence'] ?? null,
                'first_due_date'      => $data['first_due_date'],
                'final_due_date'      => $data['final_due_date'],
                'grace_period_hours'  => $data['grace_period_hours'] ?? 48,
                'status'              => Fee::STATUS_ACTIVE,
                'assigned_by'         => $admin->id,
                'assigned_at'         => now(),
            ]);

            // Create installments if plan type is installments
            if ($fee->plan_type === Fee::PLAN_INSTALLMENTS && $fee->installments_count > 0) {
                $this->createInstallments($fee);
            }

            return $fee;
        });
    }

    /**
     * Create installment records for an installment-based fee.
     */
    private function createInstallments(Fee $fee): void
    {
        $count = $fee->installments_count;
        $perInstallment = round((float) $fee->amount_total / $count, 2);
        $startDate = Carbon::parse($fee->first_due_date);

        $interval = match ($fee->installment_cadence) {
            'weekly'   => fn (Carbon $d) => $d->addWeek(),
            'biweekly' => fn (Carbon $d) => $d->addWeeks(2),
            'monthly'  => fn (Carbon $d) => $d->addMonth(),
            default    => fn (Carbon $d) => $d->addMonth(),
        };

        $runningTotal = 0;

        for ($i = 1; $i <= $count; $i++) {
            // Last installment absorbs rounding difference
            $amount = ($i === $count)
                ? (float) $fee->amount_total - $runningTotal
                : $perInstallment;

            $runningTotal += $amount;

            FeeInstallment::create([
                'fee_id'     => $fee->id,
                'sequence'   => $i,
                'amount_due' => $amount,
                'amount_paid'=> 0,
                'due_date'   => $startDate->copy(),
                'status'     => $i === 1 ? FeeInstallment::STATUS_DUE : FeeInstallment::STATUS_UPCOMING,
            ]);

            $startDate = $interval($startDate);
        }
    }

    // ==========================================
    // PAYMENT RECORDING (ADMIN)
    // ==========================================

    /**
     * Record a payment entered by admin (auto-verified).
     */
    public function recordPayment(Fee $fee, array $data, User $admin): FeePayment
    {
        return DB::transaction(function () use ($fee, $data, $admin) {
            $payment = FeePayment::create([
                'fee_id'         => $fee->id,
                'installment_id' => $data['installment_id'] ?? null,
                'fellow_id'      => $fee->fellow_id,
                'amount'         => $data['amount'],
                'method'         => $data['method'],
                'payment_date'   => $data['payment_date'],
                'source'         => FeePayment::SOURCE_ADMIN,
                'reference'      => $this->generatePaymentReference($admin),
                'notes'          => $data['notes'] ?? null,
                'status'         => FeePayment::STATUS_VERIFIED,
                'receipt_number' => $this->generateReceiptNumber(),
                'verified_by'    => $admin->id,
                'verified_at'    => now(),
            ]);

            $this->allocatePaymentToFee($fee, (float) $data['amount'], $data['installment_id'] ?? null);

            return $payment;
        });
    }

    // ==========================================
    // PAYMENT RECEIPT UPLOAD (FELLOW)
    // ==========================================

    /**
     * Fellow submits a payment receipt for verification.
     */
    public function submitPaymentReceipt(Fee $fee, array $data, User $fellow): FeePayment
    {
        return FeePayment::create([
            'fee_id'         => $fee->id,
            'installment_id' => $data['installment_id'] ?? null,
            'fellow_id'      => $fellow->id,
            'amount'         => $data['amount'],
            'method'         => $data['method'],
            'payment_date'   => $data['payment_date'],
            'source'         => FeePayment::SOURCE_FELLOW,
            'reference'      => $data['reference'] ?? null,
            'receipt_path'   => $data['receipt_path'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'status'         => FeePayment::STATUS_SUBMITTED,
        ]);
    }

    // ==========================================
    // PAYMENT VERIFICATION (ADMIN)
    // ==========================================

    /**
     * Verify (approve) a fellow-uploaded payment.
     */
    public function verifyPayment(FeePayment $payment, User $admin): FeePayment
    {
        return DB::transaction(function () use ($payment, $admin) {
            $payment->update([
                'status'         => FeePayment::STATUS_VERIFIED,
                'receipt_number' => $this->generateReceiptNumber(),
                'verified_by'    => $admin->id,
                'verified_at'    => now(),
            ]);

            $this->allocatePaymentToFee(
                $payment->fee,
                (float) $payment->amount,
                $payment->installment_id
            );

            return $payment->fresh();
        });
    }

    /**
     * Reject a fellow-uploaded payment.
     */
    public function rejectPayment(FeePayment $payment, User $admin, string $reason): FeePayment
    {
        $payment->update([
            'status'           => FeePayment::STATUS_REJECTED,
            'rejected_by'      => $admin->id,
            'rejected_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        return $payment->fresh();
    }

    /**
     * Delete a payment record and correct financial balances.
     */
    public function deletePayment(FeePayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $fee = $payment->fee;
            $installmentId = $payment->installment_id;

            // Delete the payment record
            $payment->delete();

            // If it was applied to an installment, recalculate that installment's paid amount
            if ($installmentId) {
                $installment = FeeInstallment::find($installmentId);
                if ($installment) {
                    $newPaid = $installment->payments()->where('status', FeePayment::STATUS_VERIFIED)->sum('amount');
                    $installment->update([
                        'amount_paid' => $newPaid,
                        'status' => $newPaid >= (float) $installment->amount_due
                            ? FeeInstallment::STATUS_PAID
                            : ($newPaid > 0 ? FeeInstallment::STATUS_PARTIAL : FeeInstallment::STATUS_DUE)
                    ]);
                    
                    // We also reset any future installments back to upcoming if they were prematurely activated, 
                    // but keeping them DUE is also safe and prompts collection.
                }
            }

            // Always recalculate the fee's paid amount
            if ($fee) {
                $totalVerified = $fee->payments()->where('status', FeePayment::STATUS_VERIFIED)->sum('amount');
                $fee->update(['amount_paid' => $totalVerified]);
                
                // Recalculate overall status
                $this->recalculateFeeStatus($fee->fresh());
            }
        });
    }

    // ==========================================
    // FEE WAIVER
    // ==========================================

    /**
     * Waive a fee entirely.
     */
    public function waiveFee(Fee $fee, User $admin, string $reason): Fee
    {
        $fee->update([
            'status'       => Fee::STATUS_WAIVED,
            'waived_by'    => $admin->id,
            'waived_at'    => now(),
            'waived_reason'=> $reason,
        ]);

        return $fee->fresh();
    }

    // ==========================================
    // PAYMENT ALLOCATION
    // ==========================================

    /**
     * Allocate payment amount to fee (and optionally installment).
     */
    private function allocatePaymentToFee(Fee $fee, float $amount, ?string $installmentId = null): void
    {
        // Update the specific installment if provided
        if ($installmentId) {
            $installment = FeeInstallment::find($installmentId);
            if ($installment) {
                $newPaid = min(
                    (float) $installment->amount_due,
                    (float) $installment->amount_paid + $amount
                );
                $installment->update([
                    'amount_paid' => $newPaid,
                    'status'      => $newPaid >= (float) $installment->amount_due
                        ? FeeInstallment::STATUS_PAID
                        : FeeInstallment::STATUS_PARTIAL,
                ]);

                // Activate the next upcoming installment
                $nextInstallment = FeeInstallment::where('fee_id', $fee->id)
                    ->where('status', FeeInstallment::STATUS_UPCOMING)
                    ->orderBy('sequence')
                    ->first();
                if ($nextInstallment) {
                    $nextInstallment->update(['status' => FeeInstallment::STATUS_DUE]);
                }
            }
        }

        // Always update fee-level totals
        $fee->refresh();
        $totalVerified = $fee->payments()
            ->where('status', FeePayment::STATUS_VERIFIED)
            ->sum('amount');

        $fee->update(['amount_paid' => $totalVerified]);

        $this->recalculateFeeStatus($fee->fresh());
    }

    /**
     * Recalculate fee status based on payments vs total.
     */
    public function recalculateFeeStatus(Fee $fee): void
    {
        if ($fee->status === Fee::STATUS_WAIVED) {
            return;
        }

        $paid  = (float) $fee->amount_paid;
        $total = (float) $fee->amount_total;

        if ($paid >= $total) {
            $fee->update(['status' => Fee::STATUS_PAID]);
        } elseif ($paid > 0) {
            $fee->update(['status' => Fee::STATUS_PARTIALLY_PAID]);
        } elseif ($fee->is_overdue) {
            $fee->update(['status' => Fee::STATUS_OVERDUE]);
        } else {
            $fee->update(['status' => Fee::STATUS_ACTIVE]);
        }
    }

    // ==========================================
    // REFERENCE/RECEIPT GENERATION
    // ==========================================

    /**
     * Generate fee reference: FEE-YYYYMM-NNNNNN
     */
    public function generateFeeReference(): string
    {
        $prefix = 'FEE-' . now()->format('Ym') . '-';
        $last = Fee::where('reference', 'like', $prefix . '%')
            ->orderByDesc('reference')
            ->value('reference');

        $seq = $last ? ((int) substr($last, -6)) + 1 : 1;

        return $prefix . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate receipt number: IKS-YYYYMM-NNNNNN
     */
    public function generateReceiptNumber(): string
    {
        $prefix = 'IKS-' . now()->format('Ym') . '-';
        $last = FeePayment::where('receipt_number', 'like', $prefix . '%')
            ->orderByDesc('receipt_number')
            ->value('receipt_number');

        $seq = $last ? ((int) substr($last, -6)) + 1 : 1;

        return $prefix . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate admin payment reference: ADMIN-YYYYMMDD-NNN-XXXX
     */
    private function generatePaymentReference(User $admin): string
    {
        $initials = strtoupper(collect(explode(' ', $admin->name))
            ->map(fn ($w) => substr($w, 0, 1))
            ->join(''));

        $todayCount = FeePayment::where('source', FeePayment::SOURCE_ADMIN)
            ->whereDate('created_at', today())
            ->count() + 1;

        return 'ADMIN-' . now()->format('Ymd') . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT) . '-' . substr($initials, 0, 4);
    }

    // ==========================================
    // STATISTICS
    // ==========================================

    /**
     * Get summary statistics for admin dashboard.
     */
    public function getAdminStats(): array
    {
        return [
            'total_fees'         => Fee::count(),
            'fully_paid'         => Fee::paid()->count(),
            'partially_paid'     => Fee::partiallyPaid()->count(),
            'overdue'            => Fee::overdue()->count(),
            'waived'             => Fee::waived()->count(),
            'active'             => Fee::active()->count(),
            'total_assigned'     => Fee::sum('amount_total'),
            'total_collected'    => Fee::sum('amount_paid'),
            'outstanding'        => Fee::unpaid()->selectRaw('SUM(amount_total - amount_paid) as balance')->value('balance') ?? 0,
        ];
    }

    /**
     * Get payment verification statistics.
     */
    public function getVerificationStats(): array
    {
        return [
            'pending'             => FeePayment::pending()->fromFellow()->count(),
            'approved'            => FeePayment::verified()->count(),
            'rejected'            => FeePayment::rejected()->count(),
            'pending_fees'        => FeePayment::pending()->fromFellow()
                ->whereNull('installment_id')->count(),
            'pending_installments'=> FeePayment::pending()->fromFellow()
                ->whereNotNull('installment_id')->count(),
            'total_receipts'      => FeePayment::fromFellow()->count(),
        ];
    }
}
