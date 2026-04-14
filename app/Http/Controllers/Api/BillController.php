<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class BillController extends Controller
{
    public function index(): JsonResponse
    {
        $billings = Bill::query()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'Paid');
            })
            ->latest('bill_id')
            ->get();

        return response()->json($billings);
    }

    public function show(string $billingId): JsonResponse
    {
        $bill = Bill::where('billing_id', $billingId)->first();

        if (!$bill) {
            return response()->json([
                'message' => 'Billing not found.'
            ], 404);
        }

        return response()->json([
            'billing' => $bill,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'billing_id' => 'nullable|string|max:255',
            'student_id' => 'nullable|string|max:255',
            'student_name' => 'nullable|string|max:255',
            'fee_name' => 'required|string|max:255',
            'total_fee' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric',
            'receipt_number' => 'nullable|string|max:255',
        ]);

        $billingId = $validatedData['billing_id'] ?? null;

        if ($billingId && Bill::where('billing_id', $billingId)->exists()) {
            return response()->json([
                'message' => 'Billing already exists.'
            ], 409);
        }

        $validatedData['billing_id'] = $validatedData['billing_id'] ?? null;
        $validatedData['student_id'] = $validatedData['student_id'] ?? null;
        $validatedData['student_name'] = $validatedData['student_name'] ?? null;
        $validatedData['total_amount'] = $validatedData['total_amount'] ?? null;
        $validatedData['payment_method'] = $validatedData['payment_method'] ?? null;
        $validatedData['receipt_number'] = $validatedData['receipt_number'] ?? null;

        if ($validatedData['total_amount'] === null) {
            $validatedData['balance'] = (float) $validatedData['total_fee'];
            $validatedData['status'] = 'Unpaid';
        } else {
            $validatedData['balance'] = (float) $validatedData['total_fee'] - (float) $validatedData['total_amount'];
            $validatedData['status'] = (float) $validatedData['balance'] <= 0.0 ? 'Paid' : 'Unpaid';
        }

        try {
            $bill = Bill::create($validatedData);
        } catch (QueryException $exception) {
            $errorCode = (int) ($exception->errorInfo[1] ?? 0);

            if ($errorCode !== 1048 && !str_contains($exception->getMessage(), "cannot be null")) {
                throw $exception;
            }

            $fallbackData = $validatedData;
            $fallbackData['total_amount'] = $fallbackData['total_amount'] ?? 0;
            $fallbackData['payment_method'] = $fallbackData['payment_method'] ?? 'Cash';
            $fallbackData['balance'] = (float) $fallbackData['total_fee'] - (float) $fallbackData['total_amount'];
            $fallbackData['status'] = (float) $fallbackData['balance'] <= 0.0 ? 'Paid' : 'Unpaid';

            $bill = Bill::create($fallbackData);
        }

        return response()->json([
            'message' => 'Bill created successfully!',
            'bill' => $bill,
            'payment' => $bill,
        ], 201);
    }
}
