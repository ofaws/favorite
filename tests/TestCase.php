<?php

namespace Ofaws\Favorite\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Ofaws\Favorite\FavoriteServiceProvider;
use Ofaws\Favorite\Tests\Models\User;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Ofaws\\Favorite\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->setUpDatabase($this->app);

        $this->app['router']->group([
            'namespace' => 'Ofaws\Favorite\Http\Controllers',
            'prefix' => config('favorite.prefix'),
            'middleware' => config('favorite.middleware', ['api', 'auth']),
        ], function () {
            require __DIR__.'/../routes/routes.php';
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            FavoriteServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_favorite_table.php.stub';
        $migration->up();

        config()->set('favorite.user.model', 'Ofaws\Favorite\Tests\Models\User');

        config()->set('favorite.assets', [
            'Ofaws\Favorite\Tests\Models\Book' => ['id', 'title'],
            'Ofaws\Favorite\Tests\Models\Image' => ['id', 'title'],
        ]);

        config()->set('favorite.morph_map', [
            'Ofaws\Favorite\Tests\Models\Book' => 'books',
            'Ofaws\Favorite\Tests\Models\Image' => 'images',
        ]);
    }

    /**
     * Set up the database.
     *
     * @param  Application  $app
     */
    protected function setUpDatabase($app): void
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        $schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });

        $schema->create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('overview')->nullable();
            $table->timestamps();
        });

        $schema->create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('overview')->nullable();
            $table->timestamps();
        });

        $this->testUser = User::forceCreate([
            'name' => 'John Doe',
            'email' => 'test@user.com',
            'password' => bcrypt('secret'),
        ]);
    }
}
