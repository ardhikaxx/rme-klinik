<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BillingInvoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Refund;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingService
{
    /**
     * Process invoice payment.
     */
    public function processPayment(
        BillingInvoice $invoice,
        int $paymentMethodId,
        float $amountPaid,
        ?float $amountTendered = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($invoice, $paymentMethodId, $amountPaid, $amountTendered, $notes) {
            // Lock invoice for update
            $invoice = BillingInvoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if (in_array($invoice->status, ['lunas', 'dibatalkan', 'refund'])) {
                throw ValidationException::withMessages([
                    'status' => "Tagihan ini sudah berstatus {$invoice->status_label}.",
                ]);
            }

            if ($amountPaid <= 0) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Nominal pembayaran harus lebih besar dari 0.',
                ]);
            }

            if ($amountPaid > $invoice->balance_amount) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Nominal pembayaran (Rp'.number_format($amountPaid, 0, ',', '.').') melebihi sisa tagihan (Rp'.number_format($invoice->balance_amount, 0, ',', '.').').',
                ]);
            }

            $method = PaymentMethod::findOrFail($paymentMethodId);

            $changeDue = 0;
            if ($amountTendered !== null && $amountTendered >= $amountPaid) {
                $changeDue = $amountTendered - $amountPaid;
            }

            $receiptNumber = Payment::generateNextReceiptNumber();
            $payment = Payment::create([
                'receipt_number' => $receiptNumber,
                'billing_invoice_id' => $invoice->id,
                'payment_method_id' => $method->id,
                'amount_paid' => $amountPaid,
                'amount_tendered' => $amountTendered ?? $amountPaid,
                'change_due' => $changeDue,
                'payment_time' => now(),
                'cashier_id' => auth()->id() ?? 1,
                'notes' => $notes,
            ]);

            // Update invoice balances
            $newPaid = $invoice->paid_amount + $amountPaid;
            $newBalance = max(0, $invoice->final_amount - $newPaid);
            $newStatus = $newBalance <= 0 ? 'lunas' : 'pembayaran_sebagian';

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_amount' => $newBalance,
                'status' => $newStatus,
            ]);

            // If fully paid, mark visit as selesai if all steps are completed
            if ($newStatus === 'lunas') {
                $visit = $invoice->visit;
                app(VisitService::class)->updateStatus(
                    $visit,
                    'selesai',
                    'Pembayaran tagihan telah lunas'
                );
            }

            AuditLog::log('PAYMENT_PROCESSED', 'BILLING', "Pembayaran invoice {$invoice->invoice_number} sebesar Rp".number_format($amountPaid, 0, ',', '.')." diterima dengan kuitansi {$receiptNumber}");

            return $payment;
        });
    }

    /**
     * Apply discount with reason.
     */
    public function applyDiscount(BillingInvoice $invoice, float $discountAmount, string $reason): BillingInvoice
    {
        return DB::transaction(function () use ($invoice, $discountAmount, $reason) {
            $invoice = BillingInvoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if ($discountAmount < 0 || $discountAmount > $invoice->total_amount) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Nominal diskon tidak valid.',
                ]);
            }

            $newFinal = max(0, $invoice->total_amount - $discountAmount);
            $newBalance = max(0, $newFinal - $invoice->paid_amount);
            $newStatus = $newBalance <= 0 && $invoice->paid_amount > 0 ? 'lunas' : $invoice->status;

            $invoice->update([
                'discount_amount' => $discountAmount,
                'discount_reason' => $reason,
                'final_amount' => $newFinal,
                'balance_amount' => $newBalance,
                'status' => $newStatus,
            ]);

            AuditLog::log('DISCOUNT_APPLIED', 'BILLING', 'Diskon sebesar Rp'.number_format($discountAmount, 0, ',', '.')." diberikan pada invoice {$invoice->invoice_number}. Alasan: {$reason}");

            return $invoice;
        });
    }

    /**
     * Process refund for an invoice.
     */
    public function processRefund(BillingInvoice $invoice, float $refundAmount, string $reason, ?int $paymentId = null): Refund
    {
        return DB::transaction(function () use ($invoice, $refundAmount, $reason, $paymentId) {
            $invoice = BillingInvoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if ($refundAmount <= 0 || $refundAmount > $invoice->paid_amount) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'Nominal refund tidak boleh melebihi total yang telah dibayar (Rp'.number_format($invoice->paid_amount, 0, ',', '.').').',
                ]);
            }

            $refundNumber = Refund::generateNextRefundNumber();
            $refund = Refund::create([
                'billing_invoice_id' => $invoice->id,
                'payment_id' => $paymentId,
                'refund_number' => $refundNumber,
                'amount' => $refundAmount,
                'reason' => $reason,
                'approved_by' => auth()->id() ?? 1,
                'refunded_at' => now(),
            ]);

            $newPaid = max(0, $invoice->paid_amount - $refundAmount);
            $newBalance = max(0, $invoice->final_amount - $newPaid);

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_amount' => $newBalance,
                'status' => 'refund',
            ]);

            AuditLog::log('REFUND_PROCESSED', 'BILLING', "Refund #{$refundNumber} sebesar Rp".number_format($refundAmount, 0, ',', '.')." diproses untuk invoice {$invoice->invoice_number}. Alasan: {$reason}");

            return $refund;
        });
    }
}
