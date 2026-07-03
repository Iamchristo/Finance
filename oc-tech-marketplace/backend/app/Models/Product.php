<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    protected $appends = ['active_flash_sale'];

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

    public function flashSales(): HasMany
    {
        return $this->hasMany(FlashSale::class);
    }

    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(Bundle::class, 'bundle_products');
    }

    protected function activeFlashSale(): Attribute
    {
        return Attribute::get(function () {
            if ($this->relationLoaded('flashSales')) {
                return $this->flashSales->first(fn ($sale) => $sale->starts_at->isPast() && $sale->ends_at->isFuture());
            }

            return $this->flashSales()->active()->first();
        });
    }
}
