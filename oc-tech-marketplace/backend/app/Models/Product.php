<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'title',
        'slug',
        'summary',
        'description',
        'thumbnail_url',
        'base_price',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'average_rating' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductFile::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
