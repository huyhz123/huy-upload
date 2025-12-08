<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'country',
        'avatar',
        'user_type',
        'is_active',
        'balance',
        'debt',
        'language',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
        'debt' => 'decimal:2',
    ];

    // Relationships
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function chatbotMessages()
    {
        return $this->hasMany(ChatbotMessage::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'admin');
    }

    public function scopeStaff($query)
    {
        return $query->where('user_type', 'staff');
    }

    public function scopeCustomers($query)
    {
        return $query->where('user_type', 'customer');
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    public function isStaff()
    {
        return $this->user_type === 'staff';
    }

    public function isCustomer()
    {
        return $this->user_type === 'customer';
    }
}
