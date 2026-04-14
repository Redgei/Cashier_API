<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'payment_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
         'receipt_number'  
          ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'balance' => 'decimal:2',
            ];    

    protected $hidden = [
        'year_level',
    ];
}
