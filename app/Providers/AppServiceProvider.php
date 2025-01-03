<?php

namespace App\Providers;

use App\Factories\VendingMachineStateFactory;
use App\Helpers\ChangeCalculatorHelper;
use App\Engine\VendingMachine;
use App\Presenters\VendingMachinePresenter;
use App\Repositories\VendingMachine\Interfaces\StorageInterface;
use App\Repositories\VendingMachine\SessionStorage;
use App\Services\Inventory;
use App\States\Concrete\HasMoneyState;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Config;
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

        $this->app->bind(StorageInterface::class, function ($app) {
            $vendingMachineFactory = fn() => $app->make(VendingMachine::class); // less overhead

            return new SessionStorage(
                $app->make(Session::class),
                $app->make(VendingMachineStateFactory::class),
                Config::get('vending.default_items', []),
                Config::get('vending.default_coins', []),
                $vendingMachineFactory,
            );
        });

        $this->app->bind(HasMoneyState::class, function ($app, $params) {
            return new HasMoneyState(
                $params['machine'] ?? $app->make(VendingMachine::class),
                $app->make(VendingMachinePresenter::class)
            );
        });
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
