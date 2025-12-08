<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Course extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name', 'slug', 'description', 'short_description', 'price', 'category',
        'thumbnail', 'preview_video', 'curriculum', 'duration_hours', 'level',
        'status', 'is_featured', 'enrolled_count', 'rating', 'sort_order', 'meta'
    ];

    protected $casts = [
        'curriculum' => 'array',
        'meta' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
