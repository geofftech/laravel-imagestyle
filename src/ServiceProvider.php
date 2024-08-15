<?php

namespace GeoffTech\LaravelImageStyle;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/imagestyle.php', 'imagestyle');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/imagestyle.php' => config_path('imagestyle.php'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'imagestyle');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImageStylePurgeCommand::class,
                ImageStyleCleanCommand::class,
            ]);
        }
    }
}
