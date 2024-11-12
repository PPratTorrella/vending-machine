<?php

namespace App\Providers;

use App\Helpers\ChangeCalculatorHelper;
use App\Repositories\VendingMachine\Interfaces\StorageInterface;
use App\Repositories\VendingMachine\SessionStorage;
use App\Services\Inventory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ChangeCalculatorHelper::class, function () {
            return new ChangeCalculatorHelper();
        });

        $this->app->bind(Inventory::class, function ($app) {
            return new Inventory($app->make(ChangeCalculatorHelper::class));
        });

        $this->app->bind(StorageInterface::class, SessionStorage::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
