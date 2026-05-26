<?php

namespace Webkul\PosteShipping\Providers;

use Illuminate\Support\ServiceProvider;

class PosteShippingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/carriers.php', 'carriers'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/Database/Migrations');
        $this->loadRoutesFrom(dirname(__DIR__).'/Routes/admin.php');
        $this->loadViewsFrom(dirname(__DIR__).'/Resources/views', 'poste_shipping');
    }
}
