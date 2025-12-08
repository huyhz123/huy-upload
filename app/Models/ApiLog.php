<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_type', 'ticket_id', 'endpoint', 'method', 'request_data',
        'response_data', 'response_code', 'status', 'error_message'
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
