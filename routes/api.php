<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TransactionHistoryController;
use App\Http\Controllers\Api\RefundsController;
use App\Http\Controllers\Api\ReceiptController;



// Authentication Routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// History Routes
Route::get('/history/deleted', [HistoryController::class, 'index'])->name('getDeletedHistory');
Route::get('/history/transactions', [TransactionHistoryController::class, 'index'])->name('getTransactionHistory');

// Billing Routes
Route::post('/bills', [BillController::class, 'store'])->name('createBill');
Route::get('/billings', [BillController::class, 'index'])->name('getBillings');
Route::get('/billings/{billing_id}', [BillController::class, 'show'])->name('showBilling');
Route::post('/billings/{billing_id}/pay', [PaymentController::class, 'recordBillPaymentByBillingId'])->name('payBilling');
Route::get('/billing/{billing_id}', [PaymentController::class, 'showBillingById'])->name('showBillingById');


// Payment Route
Route::get('/payments', [PaymentController::class, 'index']);
Route::post('/payments', [PaymentController::class, 'store'])->name('createPayment');
Route::put('/payments/student/{student_id}/billing/{billing_id}', [PaymentController::class, 'updateStudentBill'])->name('updateStudentBill');
Route::delete('/payments/student/{student_id}/billing/{billing_id}', [PaymentController::class, 'destroyPaidBill'])->name('deletePaidBill');
Route::get('/payments/student/{student_id}', [PaymentController::class, 'getByStudent'])->name('getBillsOfStudents');
Route::get('/payments/student/{student_id}/unpaid', [PaymentController::class, 'getUnpaidByStudent'])->name('getUnpaidBills');
Route::get('/payments/student/{student_id}/paid', [PaymentController::class, 'getPaidByStudent'])->name('getPaidBills');

Route::put('/payments/billing/{billing_id}', [PaymentController::class, 'updateByBillingId'])->name('updateBilling');
 
//receipt route
Route::get('/receipts', [ReceiptController::class, 'getAllReceipts'])->name('getAllReceipts');
Route::get('/billing/{billing_id}/receipt', [ReceiptController::class, 'generateReceipt']);
Route::delete('/receipts/{receiptNumber}', [ReceiptController::class, 'deleteReceipt'])->name('deleteReceipt');
Route::get('/receipts/{receiptNumber}', [ReceiptController::class, 'showReceipt'])->name('showReceipt');
Route::get('/student/{studentIdentifier}/billing/{billing_id}/receipt', [ReceiptController::class, 'showStudentReceipt'])->name('showStudentReceipt');  
Route::get('/student/{studentIdentifier}/{fee_name}/receipt', [ReceiptController::class, 'showStudentReceiptByFeeName'])->name('showStudentReceiptByFeeName');


//refunds route
Route::get('/refunds', [RefundsController::class, 'getAllRefunds'])->name('getAllRefunds');
Route::delete('/refunds/{billing_id}', [RefundsController::class, 'deleteRefund'])->name('deleteRefund');
Route::post('/payments/{id}/refund', [RefundsController::class, 'refundPayment'])->name('refundPayment');   
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});
