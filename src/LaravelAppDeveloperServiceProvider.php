<?php

declare(strict_types=1);

namespace StafeGroup\LaravelAppDeveloper;

use Illuminate\Support\ServiceProvider;
use StafeGroup\LaravelAppDeveloper\Console\InstallCommand;
use StafeGroup\LaravelAppDeveloper\Console\StartCommand;

class LaravelAppDeveloperServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-app-developer.php',
            'laravel-app-developer'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                StartCommand::class,
                InstallCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/laravel-app-developer.php' => config_path('laravel-app-developer.php'),
            ], 'laravel-app-developer-config');
        }
    }
}