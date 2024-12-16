<?php

return [

    /*
    | --------------------------------------------------------------------------
    | Favorite Model / Table
    |--------------------------------------------------------------------------
    |
    | This is the name of the table that will be created by the migration and used by the package.
    |
    */

    'table_name' => 'favorites',

    /*
    | --------------------------------------------------------------------------
    | User Model / Table
    |--------------------------------------------------------------------------
    |
    | We need to know which user table and model should be used to bind package models to users
    |
    */

    'user' => [

        'model' => 'App\Models\User',

        'table_name' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes Prefix / Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify which prefix the package will assign to all the routes
    | that it registers with the application.
    |
    */

    'prefix' => 'api/v1',

    'middleware' => ['api', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Assets constrains
    |--------------------------------------------------------------------------
    |
    | List assets class names and the necessary columns that should be fetched for favorites listings
    | e.g. \App\Models\Book => ['id', 'title', 'position']
    |
    */

    'assets' => [],

    /*
    |--------------------------------------------------------------------------
    | Morph map
    |--------------------------------------------------------------------------
    |
    | If you use morph map or if you will use the package controller list here the assets model and table names
    | e.g. \App\Models\Book => 'books'
    |
    */

    'morph_map' => [],
];
