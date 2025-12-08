<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class File extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name', 'slug', 'description', 'file_path', 'file_type', 'file_size',
        'price', 'category', 'thumbnail', 'preview_images', 'enable_watermark',
        'watermark_text', 'download_limit', 'token_expiry_hours', 'status',
        'is_featured', 'total_downloads', 'sort_order', 'meta'
    ];

    protected $casts = [
        'preview_images' => 'array',
        'meta' => 'array',
        'enable_watermark' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
