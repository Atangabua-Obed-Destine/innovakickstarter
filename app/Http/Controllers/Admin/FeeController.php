<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\User;
use App\Services\FeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin Fee Controller
 *
 * Handles fee reports (list, create, record payments, print receipts)
 * and payment verification (approve/reject fellow-uploaded receipts).
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class FeeController extends Controller
{
    public function __construct(
        private readonly FeeService $feeService,
    ) {}

    // ==========================================
    // FEE REPORTS
    // ==========================================

    /**
     * Fee Reports index — stat tiles + filterable table.
     */
    public function index(Request $request)
    {
        $stats = $this->feeService->getAdminStats();

        $query = Fee::with(['fellow', 'installments']);

        // Filters
        if ($search = $request->input('search')) {
            $query->whereHas('fellow', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            })->orWhere('reference', 'like', "%{$search}%")
              ->orWhere('title', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($planType = $request->input('plan_type')) {
            $query->where('plan_type', $planType);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('first_due_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('final_due_date', '<=', $dateTo);
        }

        $fees = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $filters = $request->only(['search', 'status', 'plan_type', 'date_from', 'date_to']);

        return view('admin.fees.index', compact('stats', 'fees', 'filters'));
    }

    /**
     * Create fee form.
     */
    public function create()
    {
        $fellows = User::where('role', 'fellow')
            ->whereNotNull('onboarding_completed_at')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'username']);

        return view('admin.fees.create', compact('fellows'));
    }

    /**
     * Store a new fee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fellow_id'           => 'required|exists:users,id',
            'title'               => 'required|string|max:200',
            'description'         => 'nullable|string|max:1000',
            'amount_total'        => 'required|numeric|min:1',
            'due_date'            => 'required|date',
            'grace_period_hours'  => 'nullable|integer|min:0|max:720',
            'billable_composite'  => 'nullable|string',
        ]);

        // Hardcode "one_time" payment plan logic for the underlying service
        $validated['plan_type'] = 'one_time';
        $validated['first_due_date'] = $validated['due_date'];
        $validated['final_due_date'] = $validated['due_date'];

        // Parse composite billable field (e.g., "App\Models\InternshipProfile|8")
        if (!empty($validated['billable_composite'])) {
            $parts = explode('|', $validated['billable_composite']);
            if (count($parts) === 2) {
                $validated['billable_type'] = $parts[0];
                $validated['billable_id'] = $parts[1];
            }
        }
        
        // Remove the composite field so it doesn't interfere
        unset($validated['billable_composite']);

        $fee = $this->feeService->assignFee($validated, auth()->user());

        return redirect()->route('admin.fees.show', $fee)
            ->with('success', 'Fee assigned successfully. Reference: ' . $fee->reference);
    }

    /**
     * Show a single fee with details.
     */
    public function show(Fee $fee)
    {
        $fee->load(['fellow', 'installments', 'payments.verifier', 'payments.rejector', 'assignedByAdmin', 'waivedByAdmin']);

        return view('admin.fees.show', compact('fee'));
    }

    /**
     * Record admin payment for a fee.
     */
    public function recordPayment(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'method'         => 'required|in:cash,mtn_momo,orange_money,bank_transfer',
            'payment_date'   => 'required|date',
            'installment_id' => 'nullable|exists:fee_installments,id',
            'notes'          => 'nullable|string|max:500',
        ]);

        $this->feeService->recordPayment($fee, $validated, auth()->user());

        return redirect()->route('admin.fees.show', $fee)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Waive a fee.
     */
    public function waive(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        $this->feeService->waiveFee($fee, auth()->user(), $validated['reason']);

        return redirect()->route('admin.fees.show', $fee)
            ->with('success', 'Fee has been waived.');
    }

    /**
     * Change the deadline for a fee.
     */
    public function changeDeadline(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'new_deadline' => 'required|date',
            'shift_installments' => 'nullable|boolean',
        ]);

        $this->feeService->changeFeeDeadline(
            $fee, 
            $validated['new_deadline'], 
            $validated['shift_installments'] ?? false
        );

        return redirect()->route('admin.fees.show', $fee)
            ->with('success', 'Fee deadline changed successfully.');
    }

    /**
     * Print receipt for a payment.
     */
    public function printReceipt(FeePayment $payment)
    {
        $payment->load(['fee.fellow', 'fee.installments', 'verifier']);

        return view('admin.fees.receipt', compact('payment'));
    }

    // ==========================================
    // PAYMENT VERIFICATIONS
    // ==========================================

    /**
     * Payment verification queue.
     */
    public function verifications(Request $request)
    {
        $stats = $this->feeService->getVerificationStats();

        $query = FeePayment::with(['fee', 'fellow', 'verifier']);

        // Filters
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            // Default: show pending first
            $query->orderByRaw("FIELD(status, 'submitted', 'verified', 'rejected')");
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('fellow', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($paymentType = $request->input('payment_type')) {
            if ($paymentType === 'fee') {
                $query->whereNull('installment_id');
            } elseif ($paymentType === 'installment') {
                $query->whereNotNull('installment_id');
            }
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('payment_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('payment_date', '<=', $dateTo);
        }

        $payments = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $filters = $request->only(['status', 'search', 'payment_type', 'date_from', 'date_to']);

        return view('admin.payment-verifications.index', compact('stats', 'payments', 'filters'));
    }

    /**
     * View a single payment verification.
     */
    public function showVerification(FeePayment $payment)
    {
        $payment->load(['fee.fellow', 'fee.installments', 'fellow', 'verifier', 'rejector', 'installment']);

        return view('admin.payment-verifications.show', compact('payment'));
    }

    /**
     * Approve a fellow-uploaded payment.
     */
    public function approveVerification(FeePayment $payment)
    {
        if ($payment->status !== FeePayment::STATUS_SUBMITTED) {
            return back()->with('error', 'This payment has already been processed.');
        }

        $this->feeService->verifyPayment($payment, auth()->user());

        return redirect()->route('admin.payment-verifications.show', $payment)
            ->with('success', 'Payment verified and approved. Receipt: ' . $payment->fresh()->receipt_number);
    }

    /**
     * Reject a fellow-uploaded payment.
     */
    public function rejectVerification(Request $request, FeePayment $payment)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        if ($payment->status !== FeePayment::STATUS_SUBMITTED) {
            return back()->with('error', 'This payment has already been processed.');
        }

        $this->feeService->rejectPayment($payment, auth()->user(), $validated['rejection_reason']);

        return redirect()->route('admin.payment-verifications.show', $payment)
            ->with('success', 'Payment has been rejected.');
    }

    // ==========================================
    // PUBLIC RECEIPT VERIFICATION
    // ==========================================

    /**
     * Public receipt verification page (no auth required).
     */
    public function publicVerify(string $uuid)
    {
        $payment = FeePayment::with(['fee.fellow', 'verifier'])
            ->where('verify_uuid', $uuid)
            ->where('status', FeePayment::STATUS_VERIFIED)
            ->firstOrFail();

        return view('public.receipt-verify', compact('payment'));
    }
    /**
     * Delete a fee if it has no payment records.
     */
    public function destroy(Fee $fee)
    {
        abort_if($fee->amount_paid > 0 || $fee->payments()->count() > 0, 403, 'Cannot delete a fee that has payment records.');

        // Delete associated installments first
        $fee->installments()->delete();
        
        // Delete the fee itself
        $fee->delete();

        return redirect()->route('admin.fees.index')
            ->with('success', 'Fee deleted successfully.');
    }
    /**
     * Delete a payment record and correct balances.
     */
    public function destroyPayment(FeePayment $payment)
    {
        $this->feeService->deletePayment($payment);

        return redirect()->route('admin.payment-verifications.index')
            ->with('success', 'Payment record deleted and balances updated successfully.');
    }

    /**
     * Get billable items (internships, programs) for a specific fellow.
     */
    public function getFellowBillables(User $fellow)
    {
        $billables = [];

        // 1. Internships
        $internships = \App\Models\InternshipProfile::where('user_id', $fellow->id)
            ->whereIn('status', [
                \App\Models\InternshipProfile::STATUS_APPROVED, 
                \App\Models\InternshipProfile::STATUS_ACTIVE, 
                \App\Models\InternshipProfile::STATUS_COMPLETED
            ])
            ->get();
            
        foreach ($internships as $internship) {
            $billables[] = [
                'type' => 'App\Models\InternshipProfile',
                'id' => $internship->id,
                'label' => "Internship at {$internship->institution_name}"
            ];
        }

        // 2. Programs (Current or Active)
        $programs = $fellow->programs()->get();
        foreach ($programs as $program) {
            $billables[] = [
                'type' => 'App\Models\Program',
                'id' => $program->id,
                'label' => "Program: {$program->name}"
            ];
        }

        // 3. Cohorts
        $cohorts = $fellow->cohorts()->get();
        foreach ($cohorts as $cohort) {
            $billables[] = [
                'type' => 'App\Models\Cohort',
                'id' => $cohort->id,
                'label' => "Cohort: {$cohort->name}"
            ];
        }

        return response()->json($billables);
    }
}
