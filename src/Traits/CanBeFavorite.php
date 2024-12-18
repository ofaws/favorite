<?php

namespace Ofaws\Favorite\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Ofaws\Favorite\Models\Favorite;

trait CanBeFavorite
{
    public function favorited(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'asset');
    }

    public function myFavorite(): MorphOne
    {
        return $this->morphOne(Favorite::class, 'asset')->byAuth();
    }

    public function scopeAddIsFavorite(Builder $query): void
    {
        $query->addSelect(['is_favorite' => Favorite::select('id')
            ->where('user_id', auth()->id())
            ->where(fn ($q) => $q
                ->where('asset_type', get_class($this))
                ->orWhere('asset_type', class_basename($this))
            )
            ->whereColumn('asset_id', $this->table.'.id')
            ->take(1),
        ]);
    }

    public function scopeFilterByFavorite(Builder $query): void
    {
        $query->when(request('favorited'), fn (Builder $query, bool $favorited) => $favorited
            ? $query->whereHas('favorited', fn (Builder $query) => $query->where('user_id', auth()->id()))
            : $query->whereDoesntHave('favorited', fn (Builder $query) => $query->where('user_id', auth()->id()))
        );
    }
}
