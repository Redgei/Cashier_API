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
Route::middleware('auth:sanctum')->prefix('history')->group(function(){
Route::get('/deleted', [HistoryController::class, 'index'])->name('getDeletedHistory');
Route::get('/transactions', [TransactionHistoryController::class, 'index'])->name('getTransactionHistory');
});


// Billing Routes
Route::post('/bills', [BillController::class, 'store'])->name('createBill');

Route::middleware('auth:sanctum')->prefix('billings')->group(function(){
Route::get('/', [BillController::class, 'index'])->name('getBillings');
Route::get('/{billing_id}', [BillController::class, 'show'])->name('showBilling');
Route::post('/{billing_id}/pay', [PaymentController::class, 'recordBillPaymentByBillingId'])->name('payBilling');
Route::get('/{billing_id}', [PaymentController::class, 'showBillingById'])->name('showBillingById');
Route::get('/{billing_id}/receipt', [ReceiptController::class, 'generateReceipt']);
});

// Payment Route
Route::middleware('auth:sanctum')->prefix('payments')->group(function(){
Route::get('/', [PaymentController::class, 'index']);
Route::post('/', [PaymentController::class, 'store'])->name('createPayment');
Route::put('/student/{student_id}/billing/{billing_id}', [PaymentController::class, 'updateStudentBill'])->name('updateStudentBill');
Route::delete('/student/{student_id}/billing/{billing_id}', [PaymentController::class, 'destroyPaidBill'])->name('deletePaidBill');
Route::get('/student/{student_id}', [PaymentController::class, 'getByStudent'])->name('getBillsOfStudents');
Route::get('/student/{student_id}/unpaid', [PaymentController::class, 'getUnpaidByStudent'])->name('getUnpaidBills');
Route::get('/student/{student_id}/paid', [PaymentController::class, 'getPaidByStudent'])->name('getPaidBills');
Route::put('/billing/{billing_id}', [PaymentController::class, 'updateByBillingId'])->name('updateBilling');
Route::post('/{id}/refund', [RefundsController::class, 'refundPayment'])->name('refundPayment');  
});

//receipt route
Route::middleware('auth:sanctum')->prefix('receipts')->group(function(){
Route::get('/', [ReceiptController::class, 'getAllReceipts'])->name('getAllReceipts');
Route::delete('/{receiptNumber}', [ReceiptController::class, 'deleteReceipt'])->name('deleteReceipt');
Route::get('/{receiptNumber}', [ReceiptController::class, 'showReceipt'])->name('showReceipt');
});

//public route receipts for registrar 
Route::get('/student/{studentIdentifier}/billing/{billing_id}/receipt', [ReceiptController::class, 'showStudentReceipt'])->name('showStudentReceipt');  
Route::get('/student/{studentIdentifier}/{fee_name}/receipt', [ReceiptController::class, 'showStudentReceiptByFeeName'])->name('showStudentReceiptByFeeName');


//refunds route
Route::middleware('auth:sanctum')->prefix('refunds')->group(function(){
Route::get('/', [RefundsController::class, 'getAllRefunds'])->name('getAllRefunds');
Route::delete('/{billing_id}', [RefundsController::class, 'deleteRefund'])->name('deleteRefund');
});

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});
