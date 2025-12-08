<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'user_id', 'ticket_id', 'type', 'items', 'subtotal',
        'tax', 'discount', 'total', 'status', 'due_date', 'paid_at', 'notes', 'meta'
    ];

    protected $casts = [
        'items' => 'array',
        'meta' => 'array',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function financialRecord()
    {
        return $this->hasOne(FinancialRecord::class);
    }
}
