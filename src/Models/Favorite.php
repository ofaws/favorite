<?php

namespace Ofaws\Favorite\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    protected $table = 'favorites';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'asset_id',
        'asset_type',
        'position',
    ];

    public function asset(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeWithConstrainedAsset(Builder $query): void
    {
        $query->with(['asset' => fn (MorphTo $morphTo) => $morphTo
            ->select('id')
            ->constrain(\Ofaws\Favorite\Favorite::constrainedAssets()),
        ]);
    }

    public function scopeByAuth(Builder $query): void
    {
        $query->where('user_id', auth()->id());
    }

    public function scopeBy(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }
}
