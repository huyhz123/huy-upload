<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'payment_id', 'type', 'category', 'amount',
        'cost', 'profit', 'description', 'record_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cost' => 'decimal:2',
        'profit' => 'decimal:2',
        'record_date' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
