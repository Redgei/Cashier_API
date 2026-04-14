<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HistoryLog;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;


class ReceiptController extends Controller
{
    /**
     * Store a newly created payment in storage.
     *
     * @param  \Illuminate\Http\Request 
     * @return \Illuminate\Http\JsonResponse
     */

    
public function getAllReceipts(): JsonResponse
{
    $receipts = Receipt::query()
        ->whereNotNull('receipt_number')
        ->where('receipt_number', '!=', '')
        ->orderByDesc('receipt_number')
        ->get()
        ->map(function (Receipt $receipt) {
            return [
                'payment_id' => null,
                'receipt_number' => $receipt->receipt_number,
                'billing_id' => $receipt->student_billing,
                'student_id' => $receipt->student_id,
                'student_name' => $receipt->student_name,
                'fee_name' => null,
                'total_amount' => 0,
                'payment_method' => null,
                'status' => null,
                'balance' => null,
                'issued_at' => null,
            ];
        })
        ->values();

    if ($receipts->isEmpty()) {
        $this->backfillReceiptsFromPayments();

        $receipts = Receipt::query()
            ->whereNotNull('receipt_number')
            ->where('receipt_number', '!=', '')
            ->orderByDesc('receipt_number')
            ->get()
            ->map(function (Receipt $receipt) {
                return [
                    'payment_id' => null,
                    'receipt_number' => $receipt->receipt_number,
                    'billing_id' => $receipt->student_billing,
                    'student_id' => $receipt->student_id,
                    'student_name' => $receipt->student_name,
                    'fee_name' => null,
                    'total_amount' => 0,
                    'payment_method' => null,
                    'status' => null,
                    'balance' => null,
                    'issued_at' => null,
                ];
            })
            ->values();
    }

    return response()->json([
        'total_receipts' => $receipts->count(),
        'receipts' => $receipts,
    ], 200);
}

public function generateReceipt($billing_id)
{
    $payment = Payment::where('billing_id', $billing_id)->first();

    if (!$payment) {
        return response()->json(['message' => 'Payment not found'], 404);
    }

    // Ensure receipt number exists
    if (!$payment->receipt_number) {
        $payment->receipt_number = 'RCPT-' . strtoupper(mt_rand(100000, 999999));
        $payment->save();
    }

    $this->syncReceiptRecord($payment);

    $receipt = [
        'receipt_number' => $payment->receipt_number,
        'billing_id' => $payment->billing_id,
        'student_id' => $payment->student_id,
        'student_name' => $payment->student_name,
        'fee_name' => $payment->fee_name,
        'total_fee' => $payment->total_fee,
        'total_amount' => $payment->total_amount,
        'payment_method' => $payment->payment_method,
        'status' => $payment->status,
        'balance' => $payment->balance,
        'date' => now()->format('Y-m-d H:i:s'),
    ];

    return response()->streamDownload(function () use ($receipt) {
        echo json_encode($receipt, JSON_PRETTY_PRINT);
    }, "OR-{$payment->receipt_number}.json");
}

public function showReceipt($receiptNumber)
{
    $payment = Payment::where('receipt_number', $receiptNumber)->first();

    if (!$payment) {
        return response()->json([
            'message' => 'Receipt not found'
        ], 404);
    }

    // Optional: only allow paid receipts
    if ($payment->balance > 0) {
        return response()->json([
            'message' => 'Receipt not available. Payment not complete.'
        ], 400);
    }

    return response()->json([
        'receipt_number' => $payment->receipt_number,
        'billing_id' => $payment->billing_id,
        'student_id' => $payment->student_id,
        'student_name' => $payment->student_name,
        'fee_name' => $payment->fee_name,
        'total_fee' => $payment->total_fee,
        'amount_paid' => $payment->total_amount,
        'payment_method' => $payment->payment_method,
        'status' => $payment->status,
        'balance' => $payment->balance,
        'date' => $payment->updated_at,
    ], 200);
}

public function showStudentReceipt($studentName, $billing_id): JsonResponse
{
    $payment = Payment::where('billing_id', $billing_id)->first();

    if (!$payment) {
        return response()->json([
            'message' => 'Receipt not found'
        ], 404);
    }

    $normalizedIdentifier = Str::of((string) $studentName)->trim()->lower()->toString();
    $studentIdMatches = Str::of((string) $payment->student_id)->trim()->lower()->toString() === $normalizedIdentifier;
    $studentNameMatches = Str::of((string) $payment->student_name)->trim()->lower()->toString() === $normalizedIdentifier;

    if (!$studentIdMatches && !$studentNameMatches) {
        return response()->json([
            'message' => 'Receipt not found for this student and billing ID'
        ], 404);
    }

    if ($payment->balance > 0) {
        return response()->json([
            'message' => 'Receipt not available. Payment not complete.'
        ], 400);
    }

    return response()->json([
        'receipt_number' => $payment->receipt_number,
        'billing_id' => $payment->billing_id,
        'student_id' => $payment->student_id,
        'student_name' => $payment->student_name,
        'fee_name' => $payment->fee_name,
        'total_fee' => $payment->total_fee,
        'amount_paid' => $payment->total_amount,
        'payment_method' => $payment->payment_method,
        'status' => $payment->status,
        'balance' => $payment->balance,
        'date' => $payment->updated_at,
    ], 200);
}

public function showStudentReceiptByFeeName($studentIdentifier, $fee_name): JsonResponse
{
    $normalize = function ($value): string {
        $value = urldecode((string) $value);
        $value = strtolower(trim($value));
        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value ?? '');
    };

    $normalizedIdentifier = $normalize($studentIdentifier);
    $normalizedFeeName = $normalize($fee_name);

    $payment = Payment::orderByDesc('updated_at')
        ->orderByDesc('payment_id')
        ->get()
        ->first(function (Payment $payment) use ($normalize, $normalizedIdentifier, $normalizedFeeName) {
            $studentIdMatches = $normalize($payment->student_id) === $normalizedIdentifier;
            $studentNameMatches = $normalize($payment->student_name) === $normalizedIdentifier;
            $feeNameMatches = $normalize($payment->fee_name) === $normalizedFeeName;

            return $feeNameMatches && ($studentIdMatches || $studentNameMatches);
        });

    if (!$payment) {
        return response()->json([
            'message' => 'Receipt not found for this student and fee name'
        ], 404);
    }

    if ($payment->balance > 0) {
        return response()->json([
            'message' => 'Receipt not available. Payment not complete.'
        ], 400);
    }

    return response()->json([
        'receipt_number' => $payment->receipt_number,
        'billing_id' => $payment->billing_id,
        'student_id' => $payment->student_id,
        'student_name' => $payment->student_name,
        'fee_name' => $payment->fee_name,
        'total_fee' => $payment->total_fee,
        'amount_paid' => $payment->total_amount,
        'payment_method' => $payment->payment_method,
        'status' => $payment->status,
        'balance' => $payment->balance,
        'date' => $payment->updated_at,
    ], 200);
}

public function deleteReceipt($receiptNumber): JsonResponse
{
    $payment = Payment::where('receipt_number', $receiptNumber)->first();
    $receipt = Receipt::where('receipt_number', $receiptNumber)->first();

    if (!$payment && !$receipt) {
        return response()->json([
            'message' => 'Receipt not found'
        ], 404);
    }

    HistoryLog::create([
        'category' => 'receipt',
        'name' => $payment?->student_name ?? $receipt?->student_name,
        'number' => $receiptNumber,
        'action_at' => now(),
    ]);

    if ($payment) {
        $payment->receipt_number = null;
        $payment->save();
    }

    Receipt::where('receipt_number', $receiptNumber)->delete();

    return response()->json([
        'message' => 'Receipt deleted successfully'
    ], 200);
}

private function syncReceiptRecord(Payment $payment): void
{
    $receiptNumber = trim((string) $payment->receipt_number);

    if ($receiptNumber === '') {
        return;
    }

    $existing = Receipt::where('receipt_number', $receiptNumber)->first();

    if ($existing) {
        $existing->student_billing = $payment->billing_id;
        $existing->student_name = $payment->student_name;
        $existing->student_id = $payment->student_id;
        $existing->save();
        return;
    }

    Receipt::create([
        'receipt_number' => $receiptNumber,
        'student_billing' => $payment->billing_id,
        'student_name' => $payment->student_name,
        'student_id' => $payment->student_id,
    ]);
}

private function backfillReceiptsFromPayments(): void
{
    Payment::query()
        ->whereNotNull('receipt_number')
        ->where('receipt_number', '!=', '')
        ->get()
        ->each(function (Payment $payment) {
            $this->syncReceiptRecord($payment);
        });
}

}
