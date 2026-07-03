<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'order_item_id',
        'rating',
        'comment',
        'is_verified_purchase',
        'vendor_reply',
        'helpful_votes',
        'status',
        'report_reason',
        'reported_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified_purchase' => 'boolean',
            'reported_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', 'visible');
    }

    public function scopeReported(Builder $query): Builder
    {
        return $query->whereNotNull('reported_at')->where('status', '!=', 'hidden');
    }
}
