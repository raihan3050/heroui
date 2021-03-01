<?php

namespace Deshiserver\Heroui;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class HerouiServiceProvider extends ServiceProvider
{
    protected function insertFile()
    {
        copy(base_path('composer.json'), __DIR__.'\tmp\composer.php');
        $composer_string = file_get_contents(__DIR__.'\tmp\composer.php');
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
            $composer_string = json_encode($composer_array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            file_put_contents(__DIR__.'\tmp\composer.php', $composer_string);
            copy(__DIR__.'\tmp\composer.php', base_path('composer.json'));
        }
    }
    protected function insertProviders()
    {
        if (! is_dir($directory = app_path('Providers'))) {
            mkdir($directory, 0755, true);
        }
        copy(__DIR__.'/../stubs/Providers/MenuServiceProvider.php', app_path('Providers/MenuServiceProvider.php'));

        copy(base_path('config/app.php'), __DIR__.'\tmp\app.php');
        $config_app_string = file_get_contents(__DIR__.'\tmp\app.php');
        $config_app_spliter = 'App\Providers\RouteServiceProvider::class,';
        $config_app_string_arr = explode($config_app_spliter, $config_app_string);
        $config_app_string_arr[0] = $config_app_string_arr[0].PHP_EOL.$config_app_spliter;
        $provider_list = array();
        $provider_list[] = 'App\Providers\MenuServiceProvider::class,';
        $new_provider_available = 0;
        foreach ($provider_list as $provider) {
            if (strpos($config_app_string, $provider) !== false) {
                // echo 'Already Added';
            } else {
                $new_provider_available = 1;
                $config_app_string_arr[1] = $provider.PHP_EOL.$config_app_string_arr[1];
            }
        }
        if($new_provider_available == 1){
            $config_app_string = $config_app_string_arr[0].PHP_EOL.$config_app_string_arr[1];
            file_put_contents(__DIR__.'\tmp\app.php', $config_app_string);
            copy(__DIR__.'\tmp\app.php', base_path('config/app.php'));
        }
    }

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

        $this->insertFile();
        $this->insertProviders();

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

    }
}
