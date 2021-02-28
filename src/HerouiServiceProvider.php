<?php

namespace Deshiserver\Heroui;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class HerouiServiceProvider extends ServiceProvider
{
    /**
     * Register the package services.
     *
     * @return void
     */
    public function register()
    {
        if (! is_dir($directory = app_path('Helpers'))) {
            mkdir($directory, 0755, true);
        }
        copy(__DIR__.'/../stubs/Helpers/helpers.php', app_path('Helpers/helpers.php'));

        if ($this->app->runningInConsole()) {
            $this->commands([
                AuthCommand::class,
                ControllersCommand::class,
                UiCommand::class,
            ]);
        }
        $this->mergeConfigFrom(
            __DIR__.'/../config/heroui.php', 'contact'
        );
        $this->publishes([
            __DIR__.'/../config/heroui.php' => config_path('heroui.php'),
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Route::mixin(new AuthRouteMethods);
    }
}
