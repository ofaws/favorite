<?php

namespace Ofaws\Favorite\Tests\Models;

use Ofaws\Favorite\Traits\HasFavorites;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasFavorites;

    protected $table = 'users';
}
