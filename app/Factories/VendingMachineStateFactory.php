<?php

namespace App\Factories;

use App\Engine\VendingMachine;
use App\States\Concrete\HasMoneyState;
use App\States\Concrete\IdleState;
use App\States\Interfaces\VendingMachineStateInterface;
use Exception;
use Illuminate\Support\Facades\App;

class VendingMachineStateFactory
{
    const IDLE_STATE_NAME = 'idleState';
    const HAS_MONEY_STATE_NAME = 'hasMoneyState';

    protected static array $stateMap = [
        self::IDLE_STATE_NAME => IdleState::class,
        self::HAS_MONEY_STATE_NAME => HasMoneyState::class,
    ];

    /**
     * Beware will set default message when creating state
     *
     * @param string $stateName
     * @param VendingMachine $vendingMachine
     * @return VendingMachineStateInterface
     * @throws Exception
     */
    public static function create(string $stateName, VendingMachine $vendingMachine): VendingMachineStateInterface
    {
        $stateClass = self::$stateMap[$stateName] ?? null;

        if (!$stateClass || !class_exists($stateClass)) {
            throw new Exception("Invalid or missing state: {$stateName}");
        }

        $state = App::make($stateClass, ['machine' => $vendingMachine]);

        if (!$state instanceof VendingMachineStateInterface) {
            throw new Exception("$stateClass does not implement VendingMachineStateInterface.");
        }

        return $state;
    }
}
