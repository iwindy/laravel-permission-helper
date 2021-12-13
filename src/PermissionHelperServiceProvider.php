<?php

namespace Iwindy\LaravelPermissionHelper;

use Iwindy\LaravelPermissionHelper\Console\Commands\CreatePermission;
use Illuminate\Support\ServiceProvider;

class PermissionHelperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreatePermission::class
            ]);
        }
    }
}
