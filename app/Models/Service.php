<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Service extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name', 'slug', 'description', 'short_description', 'price', 'cost_price',
        'category', 'image', 'images', 'estimated_time', 'status', 'is_featured',
        'sort_order', 'meta', 'api_type', 'api_service_id'
    ];

    protected $casts = [
        'images' => 'array',
        'meta' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
