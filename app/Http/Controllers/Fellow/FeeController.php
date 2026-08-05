<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Services\FeeService;
use Illuminate\Http\Request;

/**
 * Fellow Fee Controller
 *
 * Allows a fellow to view their assigned fees, track their balances,
 * upload payment receipts, and download verified receipts.
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class FeeController extends Controller
{
    public function __construct(
        private readonly FeeService $feeService,
    ) {}

    /**
     * List all fees for the authenticated fellow.
     */
    public function index()
    {
        $user = auth()->user();

        $fees = Fee::with(['installments', 'payments' => function ($q) {
            $q->where('status', FeePayment::STATUS_VERIFIED);
        }])
        ->where('fellow_id', $user->id)
        ->orderByDesc('created_at')
        ->get();

        $summary = [
            'total_assigned' => $fees->sum('amount_total'),
            'total_paid'     => $fees->sum('amount_paid'),
            'outstanding'    => $fees->whereIn('status', [Fee::STATUS_ACTIVE, Fee::STATUS_PARTIALLY_PAID, Fee::STATUS_OVERDUE])
                                     ->sum(fn ($f) => $f->balance),
        ];

        return view('fellow.fees.index', compact('fees', 'summary'));
    }

    /**
     * Show a specific fee details.
     */
    public function show(Fee $fee)
    {
        if ($fee->fellow_id !== auth()->id()) {
            abort(403);
        }

        $fee->load(['installments', 'payments.verifier']);

        return view('fellow.fees.show', compact('fee'));
    }

    /**
     * Show form to upload a receipt for a fee.
     */
    public function uploadForm(Fee $fee, Request $request)
    {
        if ($fee->fellow_id !== auth()->id()) {
            abort(403);
        }

        if ($fee->balance <= 0) {
            return redirect()->route('fees.show', $fee)
                ->with('warning', 'This fee is already fully paid or waived.');
        }

        $fee->load('installments');
        
        // Pre-select installment if passed via query string
        $preselectedInstallment = $request->query('installment');

        return view('fellow.fees.upload', compact('fee', 'preselectedInstallment'));
    }

    /**
     * Store the uploaded receipt.
     */
    public function uploadStore(Request $request, Fee $fee)
    {
        if ($fee->fellow_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'method'         => 'required|in:cash,mtn_momo,orange_money,bank_transfer',
            'payment_date'   => 'required|date|before_or_equal:today',
            'installment_id' => 'nullable|exists:fee_installments,id',
            'reference'      => 'nullable|string|max:100',
            'receipt_file'   => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB max
            'notes'          => 'nullable|string|max:500',
        ]);

        // Validate installment belongs to fee
        if (!empty($validated['installment_id'])) {
            $validInstallment = $fee->installments()->where('id', $validated['installment_id'])->exists();
            if (!$validInstallment) {
                return back()->withErrors(['installment_id' => 'Invalid installment selected.'])->withInput();
            }
        }

        // Store the file
        $path = $request->file('receipt_file')->store('receipts');
        $validated['receipt_path'] = $path;

        $this->feeService->submitPaymentReceipt($fee, $validated, auth()->user());

        return redirect()->route('fees.show', $fee)
            ->with('success', 'Your payment receipt has been uploaded successfully and is pending verification.');
    }

    /**
     * Download an approved receipt.
     */
    public function downloadReceipt(FeePayment $payment)
    {
        if ($payment->fellow_id !== auth()->id()) {
            abort(403);
        }

        if (!$payment->is_verified) {
            abort(403, 'Receipt has not been verified yet.');
        }

        $payment->load(['fee.fellow', 'fee.installments', 'verifier']);

        return view('admin.fees.receipt', compact('payment'));
    }
}
