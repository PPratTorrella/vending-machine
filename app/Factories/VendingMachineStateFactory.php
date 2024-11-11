<?php

namespace App\Factories;

use App\Models\VendingMachine;
use App\States\Concrete\HasMoneyState;
use App\States\Concrete\IdleState;
use App\States\Interfaces\VendingMachineState;
use Exception;

class VendingMachineStateFactory
{
    protected static array $stateMap = [
        'idleState' => IdleState::class,
        'hasMoneyState' => HasMoneyState::class,
    ];

    public static function create(string $stateName, VendingMachine $vendingMachine): VendingMachineState
    {
        $stateClass = self::$stateMap[$stateName] ?? null;

        if (!$stateClass || !class_exists($stateClass)) {
            throw new Exception("Invalid or missing state: {$stateName}");
        }

        $state = new $stateClass($vendingMachine);

        if (!$state instanceof VendingMachineState) {
            throw new Exception("{$stateClass} is not a valid VendingMachineState.");
        }

        return $state;
    }
}
