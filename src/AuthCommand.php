<?php

namespace Deshiserver\Heroui;

use Illuminate\Console\Command;

class AuthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heroui:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for basic login and registration views and routes';

    /**
     * The views that need to be exported.
     *
     * @var array
     */
    protected $views = [
        'auth/login.stub' => 'auth/login.blade.php',
        'auth/register.stub' => 'auth/register.blade.php',
        'auth/verify.stub' => 'auth/verify.blade.php',

        'auth/passwords/confirm.stub' => 'auth/passwords/confirm.blade.php',
        'auth/passwords/email.stub' => 'auth/passwords/email.blade.php',
        'auth/passwords/reset.stub' => 'auth/passwords/reset.blade.php',

        'content/home.stub' => 'content/home.blade.php',
        'content/layout-blank.stub' => 'content/layout-blank.blade.php',
        'content/layout-boxed.stub' => 'content/layout-boxed.blade.php',
        'content/layout-collapsed-menu.stub' => 'content/layout-collapsed-menu.blade.php',
        'content/layout-empty.stub' => 'content/layout-empty.blade.php',
        'content/layout-without-menu.stub' => 'content/layout-without-menu.blade.php',

        'layouts/contentLayoutMaster.stub' => 'layouts/contentLayoutMaster.blade.php',
        'layouts/detachedLayoutMaster.stub' => 'layouts/detachedLayoutMaster.blade.php',
        'layouts/fullLayoutMaster.stub' => 'layouts/fullLayoutMaster.blade.php',
        'layouts/horizontalDetachedLayoutMaster.stub' => 'layouts/horizontalDetachedLayoutMaster.blade.php',
        'layouts/horizontalLayoutMaster.stub' => 'layouts/horizontalLayoutMaster.blade.php',
        'layouts/verticalDetachedLayoutMaster.stub' => 'layouts/verticalDetachedLayoutMaster.blade.php',
        'layouts/verticalLayoutMaster.stub' => 'layouts/verticalLayoutMaster.blade.php',

        'panels/breadcrumb.stub' => 'panels/breadcrumb.blade.php',
        'panels/footer.stub' => 'panels/footer.blade.php',
        'panels/horizontalMenu.stub' => 'panels/horizontalMenu.blade.php',
        'panels/horizontalSubmenu.stub' => 'panels/horizontalSubmenu.blade.php',
        'panels/navbar.stub' => 'panels/navbar.blade.php',
        'panels/scripts.stub' => 'panels/scripts.blade.php',
        'panels/sidebar.stub' => 'panels/sidebar.blade.php',
        'panels/styles.stub' => 'panels/styles.blade.php',
        'panels/submenu.stub' => 'panels/submenu.blade.php'
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->ensureDirectoriesExist();
        $this->exportViews();
        $this->exportBackend();
        $this->info('Authentication generated successfully.');
    }

    /**
     * Create the directories for the files.
     *
     * @return void
     */
    protected function ensureDirectoriesExist()
    {
        if (! is_dir($directory = $this->getViewPath('auth/passwords'))) {
            mkdir($directory, 0755, true);
        }

        if (! is_dir($directory = $this->getViewPath('content'))) {
            mkdir($directory, 0755, true);
        }

        if (! is_dir($directory = $this->getViewPath('layouts'))) {
            mkdir($directory, 0755, true);
        }

        if (! is_dir($directory = $this->getViewPath('panels'))) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Export the authentication views.
     *
     * @return void
     */
    protected function exportViews()
    {
        foreach ($this->views as $key => $value) {
            if (file_exists($view = $this->getViewPath($value)) && ! $this->option('force')) {
                if (! $this->confirm("The [{$value}] view already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            copy(
                __DIR__.'/Auth/bootstrap-stubs/'.$key,
                $view
            );
        }
    }

    /**
     * Export the authentication backend.
     *
     * @return void
     */
    protected function exportBackend()
    {
        $this->callSilent('heroui:controllers');

        $controller = app_path('Http/Controllers/HomeController.php');

        if (file_exists($controller)) {
            if ($this->confirm("The [HomeController.php] file already exists. Do you want to replace it?")) {
                file_put_contents($controller, $this->compileControllerStub());
            }
        } else {
            file_put_contents($controller, $this->compileControllerStub());
        }

        file_put_contents(
            base_path('routes/web.php'),
            file_get_contents(__DIR__.'/Auth/stubs/routes.stub'),
            FILE_APPEND
        );

        copy(
            __DIR__.'/../stubs/migrations/2014_10_12_100000_create_password_resets_table.php',
            base_path('database/migrations/2014_10_12_100000_create_password_resets_table.php')
        );
    }

    /**
     * Compiles the "HomeController" stub.
     *
     * @return string
     */
    protected function compileControllerStub()
    {
        return str_replace(
            '{{namespace}}',
            $this->laravel->getNamespace(),
            file_get_contents(__DIR__.'/Auth/stubs/controllers/HomeController.stub')
        );
    }

    /**
     * Get full view path relative to the application's configured view path.
     *
     * @param  string  $path
     * @return string
     */
    protected function getViewPath($path)
    {
        return implode(DIRECTORY_SEPARATOR, [
            config('view.paths')[0] ?? resource_path('views'), $path,
        ]);
    }
}
