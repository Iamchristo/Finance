<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'prefix',
        'hashed_key',
        'last_used_at',
        'revoked_at',
    ];

    protected $hidden = ['hashed_key'];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * @return array{model: ApiKey, plainTextKey: string}
     */
    public static function generate(User $user, string $name): array
    {
        $prefix = 'octk_'.Str::lower(Str::random(8));
        $secret = Str::random(32);
        $plainTextKey = "{$prefix}.{$secret}";

        $model = static::create([
            'user_id' => $user->id,
            'name' => $name,
            'prefix' => $prefix,
            'hashed_key' => hash('sha256', $plainTextKey),
        ]);

        return ['model' => $model, 'plainTextKey' => $plainTextKey];
    }

    public static function findByPlainTextKey(string $plainTextKey): ?self
    {
        return static::active()->where('hashed_key', hash('sha256', $plainTextKey))->first();
    }
}
