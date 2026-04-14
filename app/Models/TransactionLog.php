<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    use HasFactory;

    protected $table = 'transaction_logs';
    protected $primaryKey = 'transaction_log_id';
    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'billing_id',
        'student_id',
        'student_name',
        'fee_name',
        'event_type',
        'total_fee',
        'total_amount',
        'balance',
        'refund_amount',
        'payment_method',
        'status',
        'receipt_number',
        'payment_created_at',
        'payment_updated_at',
        'action_at',
    ];

    protected $casts = [
        'total_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'payment_created_at' => 'datetime',
        'payment_updated_at' => 'datetime',
        'action_at' => 'datetime',
    ];

    protected $hidden = [
        'year_level',
    ];
}
