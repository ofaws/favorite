<?php

namespace Ofaws\Favorite;

use Illuminate\Support\Facades\Route;
use ofaws\Favorite\Commands\FavoriteCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FavoriteServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('favorite')
            ->hasConfigFile()
            ->hasMigration('create_favorite_table')
            ->hasCommand(FavoriteCommand::class)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations();
            });
    }

    public function packageBooted(): void
    {
        $this->configureRoutes();
    }

    /**
     * Configure the routes offered by the application.
     */
    protected function configureRoutes(): void
    {
        if (Favorite::$registersRoutes) {
            Route::group([
                'namespace' => 'Ofaws\Favorite\Http\Controllers',
                'prefix' => config('favorite.prefix'),
                'middleware' => config('favorite.middleware', ['api', 'auth']),
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
            });
        }
    }
}
