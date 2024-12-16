<?php

namespace Ofaws\Favorite\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Ofaws\Favorite\Traits\CanBeFavorite;

class Image extends Model
{
    use CanBeFavorite;

    protected $table = 'images';

    protected $fillable = ['title', 'overview'];
}
