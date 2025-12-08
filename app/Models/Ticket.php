<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number', 'user_id', 'service_id', 'assigned_to', 'device_name',
        'device_model', 'imei', 'issue_description', 'notes', 'quoted_price',
        'final_price', 'status', 'priority', 'estimated_completion', 'completed_at',
        'delivered_at', 'api_order_id', 'api_status', 'api_response', 'meta'
    ];

    protected $casts = [
        'api_response' => 'array',
        'meta' => 'array',
        'quoted_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'estimated_completion' => 'datetime',
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function apiLogs()
    {
        return $this->hasMany(ApiLog::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}
