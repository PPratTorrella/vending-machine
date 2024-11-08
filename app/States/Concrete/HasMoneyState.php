<?php

namespace App\States\Concrete;

use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;

class HasMoneyState implements VendingMachineState
{
    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
    }

    public function insertCoin($coin)
    {
        $this->machine->moneyManager->insertCoin($coin);
    }

    public function returnCoins()
    {
        return $this->machine->moneyManager->returnCoins();
    }

    public function selectItem($itemCode)
    {
    }

    public function service($serviceCommand)
    {
    }
}
