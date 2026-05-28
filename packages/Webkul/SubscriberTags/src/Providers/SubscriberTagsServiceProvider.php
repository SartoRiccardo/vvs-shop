<?php

namespace Webkul\SubscriberTags\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\SubscriberTags\Listeners\AssignTagsOnPurchase;

class SubscriberTagsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/admin-menu.php',
            'menu.admin'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/Database/Migrations');
        $this->loadRoutesFrom(dirname(__DIR__).'/Routes/admin.php');
        $this->loadViewsFrom(dirname(__DIR__).'/Resources/views', 'subscriber_tags');

        Event::listen('checkout.order.save.after', AssignTagsOnPurchase::class);

        // Inject tag panel into product edit form
        Event::listen(
            'bagisto.admin.catalog.product.edit.form.column_2.after',
            function ($viewRenderEventManager) {
                $viewRenderEventManager->addTemplate('subscriber_tags::products.tags');
            }
        );
    }
}
