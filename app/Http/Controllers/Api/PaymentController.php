<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HistoryLog;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
class PaymentController extends Controller
{
    /**
     * Store a newly created payment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $isBillPaymentRequest = $request->has('billing_id')
            && $request->has('amount')
            && !$request->hasAny(['student_id', 'student_name', 'fee_name', 'total_fee', 'total_amount', 'payment_method']);

        if ($isBillPaymentRequest) {
            $validatedData = $request->validate([
                'billing_id' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
            ]);

            $bill = Bill::where('billing_id', $validatedData['billing_id'])->first();

            if (!$bill) {
                return response()->json([
                    'message' => 'Billing not found.'
                ], 404);
            }

            return $this->createBillPayment($bill, (float) $validatedData['amount']);
        }

        $validatedData = $request->validate([
            'billing_id' => 'nullable|string|max:255',
            'student_id' => 'required|string|max:255',
            'student_name' => 'required|string|max:255',
            'fee_name' => 'required|string|max:255',
            'total_fee' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:255',
            'status' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric',
        ]);

        if (!empty($validatedData['billing_id']) && Payment::where('billing_id', $validatedData['billing_id'])->exists()) {
            return response()->json([
                'message' => 'Billing already in charge'
            ], 409);
        }

        $validatedData['year_level'] = 'N/A';
        $validatedData['balance'] = (float) $validatedData['total_fee'] - (float) $validatedData['total_amount'];
        $validatedData['status'] = (float) $validatedData['balance'] <= 0.00 ? 'Paid' : 'Unpaid';

        $payment = Payment::forceCreate($validatedData);

        return response()->json([
            'message' => 'Payment accepted successfully!',
            'payment' => $payment,
        ], 201);
    }

    public function recordBillPaymentByBillingId(Request $request, string $billing_id): JsonResponse
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $bill = Bill::where('billing_id', $billing_id)->first();

        if (!$bill) {
            return response()->json([
                'message' => 'Billing not found.'
            ], 404);
        }

        return $this->createBillPayment($bill, (float) $validatedData['amount']);
    }

    private function createBillPayment(Bill $bill, float $appliedAmount): JsonResponse
    {
        $currentTotalAmount = (float) $bill->total_amount;
        $newTotalAmount = $currentTotalAmount + $appliedAmount;
        $balance = (float) $bill->total_fee - $newTotalAmount;
        $status = $balance <= 0.0 ? 'Paid' : 'Partial';

        $payment = Payment::where('billing_id', $bill->billing_id)->first();

        $paymentData = [
            'billing_id' => $bill->billing_id,
            'student_id' => $bill->student_id,
            'student_name' => $bill->student_name,
            'fee_name' => $bill->fee_name,
            'total_fee' => $bill->total_fee,
            'total_amount' => $newTotalAmount,
            'payment_method' => $bill->payment_method ?: 'Cash',
            'status' => $status,
            'balance' => $balance,
            'receipt_number' => $payment?->receipt_number,
            'year_level' => 'N/A',
        ];

        if ($payment) {
            $payment->forceFill($paymentData);
            $payment->save();
        } else {
            $payment = Payment::forceCreate($paymentData);
        }

        $bill->total_amount = $newTotalAmount;
        $bill->balance = $balance;
        $bill->status = $status;
        $bill->save();

        if ($status === 'Paid') {
            HistoryLog::create([
                'category' => 'payment',
                'name' => $bill->student_name,
                'number' => $bill->billing_id,
                'action_at' => now(),
            ]);

            $bill->delete();
        }

        return response()->json([
            'message' => 'Payment recorded successfully!',
            'payment' => $payment,
            'bill' => $bill,
        ], 201);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
public function index(): JsonResponse
    {
        $payments = Payment::all();

        return response()->json($payments);
    }

    /**
     * Update the specified payment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $student_id
     * @return \Illuminate\Http\JsonResponse
     */
public function update(Request $request, $student_id): JsonResponse
    {
        $request->validate([
            'status' => 'sometimes|required|string|max:255',
            'balance' => 'sometimes|required|numeric',
        ]);

        $payment = Payment::where('student_id', $student_id)
                            ->latest()
                            ->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found for this student.'], 404);
        }

        if ($request->has('balance')) {
            $payment->balance = $request->input('balance');
            if ((float)$payment->balance === 0.00) {
                $payment->status = 'Paid';
            } else {
                $payment->status = 'Unpaid';
            }
        }

        if ($request->has('status') && (float)$payment->balance !== 0.00) {
            $payment->status = $request->input('status');
        }

        $payment->save();

        return response()->json([
            'message' => 'Payment updated successfully!',
            'payment' => $payment,
        ]);
    }

    // /**
    //  * Update the specified payment in storage using student_id and billing_id.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  string  $student_id
    //  * @param  string  $billing_id
    //  * @return \Illuminate\Http\JsonResponse
    //  */
// public function updateByBillingId(Request $request, $student_id, $billing_id): JsonResponse
//     {
//         $request->validate([
//             'status' => 'sometimes|required|string|max:255',
//             'balance' => 'sometimes|required|numeric|min:0',
//         ]);

//         $payment = Payment::where('student_id', $student_id)
//                             ->where('billing_id', $billing_id)
//                             ->first();

//         if (!$payment) {
//             return response()->json(['message' => 'Payment not found for this student and billing ID.'], 404);
//         }

//         if ($request->has('balance')) {
//             $payment->balance = $request->input('balance');
//             if ((float)$payment->balance === 0.00) {
//                 $payment->status = 'Paid';
//             }
//         }

//         if ($request->has('status') && (float)$payment->balance !== 0.00) {
//             $payment->status = $request->input('status');
//         }

//         $payment->save();

//         return response()->json([
//             'message' => 'Payment updated successfully!',
//             'payment' => $payment,
//         ]);
//     }

//     /**
//      * Delete the specified paid bill from storage.
//      *
//      * @param  string  $student_id
//      * @param  string  $billing_id
//      * @return \Illuminate\Http\JsonResponse
//      */

public function updateStudentBill(Request $request, $student_id, $billing_id)
{
    // Find the bill
    $payment = Payment::where('student_id', $student_id)
        ->where('billing_id', $billing_id)
        ->first();

    if (!$payment) {
        return response()->json(['message' => 'Billing not found'], 404);
    }

    // Validate incoming data
    $validated = $request->validate([
        'total_amount' => 'sometimes|numeric|min:0',
        'payment_method' => 'sometimes|string|max:255',
    ]);

    // Update the fields if provided
    if (isset($validated['total_amount'])) {
        $payment->total_amount = $validated['total_amount'];
    }

    if (isset($validated['payment_method'])) {
        $payment->payment_method = $validated['payment_method'];
    }

    // Recompute balance and status
    $payment->balance = $payment->total_fee - $payment->total_amount;
    $payment->status = $payment->balance <= 0 ? 'Paid' : 'Unpaid';

    // Save changes
    $payment->save();

    return response()->json([
        'message' => 'Student bill updated successfully',
        'payment' => $payment
    ]);
}

public function getByStudent($student_id): JsonResponse
{
    $payments = Payment::where('student_id', $student_id)->get();

    if ($payments->isEmpty()) {
        return response()->json([
            'message' => 'No billing records found for this student.'
        ], 404);
    }

    return response()->json([
        'student_id' => $student_id,
        'total_records' => $payments->count(),
        'payments' => $payments
    ], 200);
}

public function destroyPaidBill($student_id, $billing_id): JsonResponse
    {
        $payment = Payment::where('student_id', $student_id)
                            ->where('billing_id', $billing_id)
                            ->whereIn('status', ['Paid', 'Refunded'])
                            ->first();

        if (!$payment) {
            return response()->json(['message' => 'Paid or refunded bill not found for this student and billing ID.'], 404);
        }

        HistoryLog::create([
            'category' => 'payment',
            'name' => $payment->student_name,
            'number' => $payment->billing_id,
            'action_at' => now(),
        ]);

        $payment->delete();

        return response()->json(['message' => 'Paid bill deleted successfully.'], 200);
    }

public function getUnpaidByStudent($student_id): JsonResponse
{
    $payments = Payment::where('student_id', $student_id)
                        ->where('balance', '>', 0) 
                        ->latest()
                        ->get();

    if ($payments->isEmpty()) {
        return response()->json([
            'message' => 'No unpaid billings found for this student.'
        ], 404);
    }

    return response()->json([
        'student_id' => $student_id,
        'total_unpaid' => $payments->count(),
        'payments' => $payments
    ], 200);
}

public function getPaidByStudent($student_id): JsonResponse
{
    $payments = Payment::where('student_id', $student_id)
                        ->where('balance', '<=', 0) 
                        ->latest()
                        ->get();

    if ($payments->isEmpty()) {
        return response()->json([
            'message' => 'No paid billings found for this student.'
        ], 404);
    }

    return response()->json([
        'student_id' => $student_id,
        'total_paid' => $payments->count(),
        'payments' => $payments
    ], 200);
}

public function showBillingById($billing_id)
{
    // Retrieve the billing by ID
    $billing = Payment::where('billing_id', $billing_id)->first();

    if (!$billing) {
        return response()->json([
            'message' => 'Billing not found'
        ], 404);
    }

    return response()->json([
        'billing' => $billing
    ], 200);
}


public function recordPayment(Request $request, $billing_id)
{
    $payment = Payment::where('billing_id', $billing_id)->first();

    if (!$payment) {
        return response()->json(['message' => 'Billing not found'], 404);
    }

    // Generate receipt number if it doesn't exist
    if (!$payment->receipt_number) {
        $payment->receipt_number = 'OR-' . strtoupper(Str::random(8)); // OR-1A2B3C4D
        $payment->status = 'PAID';
        $payment->balance = 0;
        $payment->save();
    }

    return response()->json([
        'message' => 'Payment recorded successfully',
        'receipt_number' => $payment->receipt_number,
        'payment' => $payment
    ], 200);
}
}
