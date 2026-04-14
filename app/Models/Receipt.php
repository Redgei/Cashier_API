<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $table = 'receipts';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'receipt_number';

    protected $keyType = 'string';

    protected $fillable = [
        'receipt_number',
        'student_billing',
        'student_name',
        'student_id',
    ];
}
