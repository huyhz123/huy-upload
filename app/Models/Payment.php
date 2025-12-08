<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id', 'user_id', 'invoice_id', 'amount', 'currency',
        'payment_method', 'status', 'gateway_transaction_id', 'gateway_response',
        'notes', 'paid_at', 'meta'
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'meta' => 'array',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function financialRecord()
    {
        return $this->hasOne(FinancialRecord::class);
    }
}
