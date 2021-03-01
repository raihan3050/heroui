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

        copy(base_path('composer.json'), __DIR__.'\composer.php');
        $composer_string = file_get_contents(__DIR__.'\composer.php');
        $composer_new_array = array();
        $composer_array = json_decode($composer_string, true);

        $autoload = array();
        $can_replace_composer_file = 0;
        if(isset($composer_array['autoload'])){
            $composer_autoload = $composer_array['autoload'];
            if(isset($composer_autoload['files'])){
                $autoload['files'] = $composer_autoload['files'];
                if(!in_array('app/Helpers/helpers.php', $autoload['files'])){
                    $can_replace_composer_file = 1;
                    $autoload['files'][count($autoload)] = 'app/Helpers/helpers.php';
                }
            } else {
                $can_replace_composer_file = 1;
                $autoload['files'][0] = 'app/Helpers/helpers.php';
            }
        }
        if($can_replace_composer_file==1){
            $composer_array['autoload']['files'] = $autoload['files'];
            $composer_string = json_encode($composer_array, JSON_PRETTY_PRINT);
            file_put_contents(__DIR__.'\composer.php', $composer_string);
            copy(__DIR__.'\composer.php', base_path('composer.json'));
        }

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
