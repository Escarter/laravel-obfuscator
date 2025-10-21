<?php

namespace Escarter\LaravelObfuscator;

use Illuminate\Support\ServiceProvider;
use Escarter\LaravelObfuscator\Commands\ObfuscateCommand;

class ObfuscatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/obfuscator.php', 'obfuscator'
        );

        $this->app->singleton('obfuscator', function ($app) {
            return new Services\ObfuscatorService($app['config']['obfuscator']);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration file
            $this->publishes([
                __DIR__.'/../config/obfuscator.php' => config_path('obfuscator.php'),
            ], 'obfuscator-config');

            // Register commands
            $this->commands([
                ObfuscateCommand::class,
            ]);
        }
    }
}

