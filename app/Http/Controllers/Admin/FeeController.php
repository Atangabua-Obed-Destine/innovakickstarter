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
            'plan_type'           => 'required|in:one_time,installments',
            'installments_count'  => 'required_if:plan_type,installments|nullable|integer|min:2|max:12',
            'installment_cadence' => 'required_if:plan_type,installments|nullable|in:weekly,biweekly,monthly',
            'first_due_date'      => 'required|date',
            'final_due_date'      => 'required|date|after_or_equal:first_due_date',
            'grace_period_hours'  => 'nullable|integer|min:0|max:720',
            'billable_type'       => 'nullable|string',
            'billable_id'         => 'nullable|string',
        ]);

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

        $query = FeePayment::with(['fee', 'fellow', 'verifier'])
            ->where('source', FeePayment::SOURCE_FELLOW);

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
}
