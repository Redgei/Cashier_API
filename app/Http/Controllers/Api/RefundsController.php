<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HistoryLog;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RefundsController extends Controller
{
    public function getAllRefunds(): JsonResponse
    {
        $refunds = Payment::where('status', 'Refunded')
            ->latest()
            ->get()
            ->map(function (Payment $payment) {
                return [
                    'payment_id' => $payment->payment_id,
                    'billing_id' => $payment->billing_id,
                    'student_id' => $payment->student_id,
                    'student_name' => $payment->student_name,
                    'fee_name' => $payment->fee_name,
                    'total_fee' => $payment->total_fee,
                    'total_amount' => $payment->total_amount,
                    'refund_amount' => max((float) $payment->total_amount - (float) $payment->total_fee, 0),
                    'payment_method' => $payment->payment_method,
                    'balance' => $payment->balance,
                    'status' => $payment->status,
                    'receipt_number' => $payment->receipt_number,
                    'refunded_at' => $payment->updated_at,
                ];
            })
            ->values();

        return response()->json([
            'total_refunds' => $refunds->count(),
            'refunds' => $refunds,
        ], 200);
    }

    /**
     * Refund only the overpaid portion of a billing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function refundPayment(Request $request, $id): JsonResponse
    {
        $payment = Payment::where('billing_id', $id)
            ->orWhere('receipt_number', $id)
            ->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found'
            ], 404);
        }

        if ($payment->balance > 0) {
            return response()->json([
                'message' => 'Cannot refund unpaid billing'
            ], 400);
        }

        if ((float) $payment->balance === 0.0) {
            return response()->json([
                'message' => 'No refundable overpayment found for this billing'
            ], 400);
        }

        if ($payment->status === 'Refunded') {
            return response()->json([
                'message' => 'Payment already refunded'
            ], 409);
        }

        $refundAmount = abs((float) $payment->balance);

        $payment->balance = 0;
        $payment->status = 'Refunded';
        $payment->save();

        return response()->json([
            'message' => 'Refund processed successfully',
            'refund_amount' => $refundAmount,
            'payment' => $payment
        ], 200);
    }

    public function deleteRefund($billing_id): JsonResponse
    {
        $refund = Payment::where('status', 'Refunded')
            ->where('billing_id', $billing_id)
            ->first();

        if (!$refund) {
            return response()->json([
                'message' => 'Refund record not found'
            ], 404);
        }

        HistoryLog::create([
            'category' => 'refund',
            'name' => $refund->student_name,
            'number' => $refund->billing_id,
            'action_at' => now(),
        ]);

        $refund->delete();

        return response()->json([
            'message' => 'Refund deleted successfully'
        ], 200);
    }

    public function getRefundedStudents(): JsonResponse
    {
        $refundedPayments = Payment::where('status', 'Refunded')
            ->latest()
            ->get();

        if ($refundedPayments->isEmpty()) {
            return response()->json([
                'message' => 'No refunded students found.'
            ], 404);
        }

        $students = $refundedPayments->map(function (Payment $payment) {
            $refundAmount = max((float) $payment->total_amount - (float) $payment->total_fee, 0);

            return [
                'payment_id' => $payment->payment_id,
                'billing_id' => $payment->billing_id,
                'student_id' => $payment->student_id,
                'student_name' => $payment->student_name,
                'fee_name' => $payment->fee_name,
                'payment_method' => $payment->payment_method,
                'total_fee' => $payment->total_fee,
                'total_amount' => $payment->total_amount,
                'refund_amount' => $refundAmount,
                'balance' => $payment->balance,
                'status' => $payment->status,
                'receipt_number' => $payment->receipt_number,
                'refunded_at' => $payment->updated_at,
            ];
        });

        return response()->json([
            'total_refunded_students' => $students->pluck('student_id')->unique()->count(),
            'total_refunded_records' => $students->count(),
            'students' => $students->values(),
        ], 200);
    }
}
