<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name', 'slug', 'description', 'short_description', 'price', 'cost_price',
        'sku', 'category', 'image', 'images', 'stock_quantity', 'low_stock_alert',
        'status', 'is_featured', 'sort_order', 'meta'
    ];

    protected $casts = [
        'images' => 'array',
        'meta' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}
