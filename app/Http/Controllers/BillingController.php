<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(protected BillingService $billingService) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $date = $request->query('date');

        $query = BillingInvoice::with(['patient', 'visit.polyclinic', 'payments'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $invoices = $query->paginate($this->perPage($request));
        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return view('billing.index', compact('invoices', 'paymentMethods', 'status', 'date'));
    }

    public function show(BillingInvoice $invoice): View
    {
        $invoice->load([
            'patient',
            'visit.polyclinic',
            'visit.doctor',
            'items',
            'payments.paymentMethod',
            'payments.cashier',
            'refunds.approver',
        ]);

        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return view('billing.show', compact('invoice', 'paymentMethods'));
    }

    public function pay(Request $request, BillingInvoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount_paid' => 'required|numeric|min:1',
            'amount_tendered' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $payment = $this->billingService->processPayment(
            $invoice,
            $data['payment_method_id'],
            $data['amount_paid'],
            $data['amount_tendered'] ?? null,
            $data['notes'] ?? null
        );

        return redirect()->route('billing.show', $invoice->id)
            ->with('success', 'Pembayaran sebesar Rp'.number_format($payment->amount_paid, 0, ',', '.')." berhasil diproses. Kuitansi No: {$payment->receipt_number}");
    }

    public function discount(Request $request, BillingInvoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'discount_amount' => 'required|numeric|min:0',
            'discount_reason' => 'required|string',
        ]);

        $this->billingService->applyDiscount($invoice, $data['discount_amount'], $data['discount_reason']);

        return back()->with('success', 'Diskon tagihan berhasil diterapkan.');
    }

    public function refund(Request $request, BillingInvoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'refund_amount' => 'required|numeric|min:1',
            'reason' => 'required|string',
            'payment_id' => 'nullable|exists:payments,id',
        ]);

        $refund = $this->billingService->processRefund(
            $invoice,
            $data['refund_amount'],
            $data['reason'],
            $data['payment_id'] ?? null
        );

        return redirect()->route('billing.show', $invoice->id)
            ->with('success', "Refund No: {$refund->refund_number} sebesar Rp".number_format($refund->amount, 0, ',', '.').' berhasil diproses.');
    }

    public function printReceipt(Payment $payment, Request $request): View
    {
        $payment->load(['invoice.patient', 'invoice.visit.doctor', 'invoice.visit.polyclinic', 'invoice.items', 'paymentMethod', 'cashier']);
        $format = $request->query('format', 'thermal'); // thermal (58/80mm) or a4

        return view('billing.receipt', compact('payment', 'format'));
    }
}
