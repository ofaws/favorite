<?php

use Illuminate\Support\Facades\Route;
use Ofaws\Favorite\Http\Controllers\FavoriteAssetController;

Route::apiResource('favorite', FavoriteAssetController::class);
