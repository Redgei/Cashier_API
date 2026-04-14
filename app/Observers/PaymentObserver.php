<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\TransactionLog;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $this->recordTransaction($payment, 'created');
    }

    public function updated(Payment $payment): void
    {
        if ($this->isRefund($payment)) {
            $this->recordTransaction($payment, 'refund');
            return;
        }

        if (! $payment->wasChanged(['total_amount', 'payment_method'])) {
            return;
        }

        $this->recordTransaction($payment, 'updated');
    }

    private function recordTransaction(Payment $payment, string $eventType): void
    {
        TransactionLog::forceCreate([
            'payment_id' => $payment->payment_id,
            'billing_id' => $payment->billing_id,
            'student_id' => $payment->student_id,
            'student_name' => $payment->student_name,
            'year_level' => 'N/A',
            'fee_name' => $payment->fee_name,
            'event_type' => $eventType,
            'total_fee' => $payment->total_fee,
            'total_amount' => $payment->total_amount,
            'balance' => $payment->balance,
            'refund_amount' => $eventType === 'refund' ? abs((float) $payment->getOriginal('balance')) : null,
            'payment_method' => $payment->payment_method,
            'status' => $payment->status,
            'receipt_number' => $payment->receipt_number,
            'payment_created_at' => $payment->created_at,
            'payment_updated_at' => $payment->updated_at,
            'action_at' => now(),
        ]);
    }

    private function isRefund(Payment $payment): bool
    {
        return strtolower(trim((string) $payment->status)) === 'refunded';
    }
}
