<?php

namespace Ofaws\Favorite\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Ofaws\Favorite\Traits\CanBeFavorite;

class Book extends Model
{
    use CanBeFavorite;

    protected $table = 'books';

    protected $fillable = ['title', 'overview'];
}
