<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionLog;
use Illuminate\Http\JsonResponse;

class TransactionHistoryController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = TransactionLog::orderByDesc('action_at')
            ->orderByDesc('transaction_log_id')
            ->get();

        return response()->json([
            'total_transactions' => $logs->count(),
            'created_transactions' => $logs->where('event_type', 'created')->count(),
            'updated_transactions' => $logs->where('event_type', 'updated')->count(),
            'refund_transactions' => $logs->where('event_type', 'refund')->count(),
            'transactions' => $logs->map(function (TransactionLog $log) {
                return [
                    'transaction_id' => $log->transaction_log_id,
                    'payment_id' => $log->payment_id,
                    'billing_id' => $log->billing_id,
                    'student_id' => $log->student_id,
                    'student_name' => $log->student_name,
                    'fee_name' => $log->fee_name,
                    'payment_method' => $log->payment_method,
                    'status' => $log->status,
                    'balance' => $log->balance,
                    'total_fee' => $log->total_fee,
                    'total_amount' => $log->total_amount,
                    'refund_amount' => $log->refund_amount,
                    'receipt_number' => $log->receipt_number,
                    'event_type' => $log->event_type,
                    'created_at' => optional($log->payment_created_at)->toISOString(),
                    'updated_at' => optional($log->payment_updated_at)->toISOString(),
                    'action_at' => optional($log->action_at)->toISOString(),
                ];
            })->values(),
        ], 200);
    }
}
