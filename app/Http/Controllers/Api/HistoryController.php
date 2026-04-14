<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HistoryLog;
use Illuminate\Http\JsonResponse;

class HistoryController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = HistoryLog::orderByDesc('action_at')
            ->orderByDesc('history_id')
            ->get();

        return response()->json([
            'payments' => $this->formatHistory($logs, 'payment'),
            'receipts' => $this->formatHistory($logs, 'receipt'),
            'refunds' => $this->formatHistory($logs, 'refund'),
        ], 200);
    }

    private function formatHistory($logs, string $category): array
    {
        return $logs
            ->where('category', $category)
            ->values()
            ->map(function (HistoryLog $log) {
                return [
                    'history_id' => $log->history_id,
                    'name' => $log->name,
                    'number' => $log->number,
                    'date' => optional($log->action_at)->toISOString(),
                ];
            })
            ->all();
    }
}
