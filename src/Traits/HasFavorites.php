<?php

namespace Ofaws\Favorite\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Ofaws\Favorite\Models\Favorite;

trait HasFavorites
{
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }
}
