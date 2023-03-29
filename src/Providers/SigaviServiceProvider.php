<?php

namespace Agenciafmd\Sigavi\Providers;

use Illuminate\Support\ServiceProvider;

class SigaviServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 
    }

    public function register()
    {
        $this->loadConfigs();
    }

    protected function loadConfigs()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-sigavi.php', 'laravel-sigavi');
    }
}
