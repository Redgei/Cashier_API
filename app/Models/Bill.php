<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $table = 'bills';
    protected $primaryKey = 'bill_id';

    protected $fillable = [
        'billing_id',
        'student_id',
        'student_name',
        'fee_name',
        'total_fee',
        'total_amount',
        'payment_method',
        'status',
        'balance',
        'receipt_number',
    ];

    protected $casts = [
        'total_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];
}
