<?php

namespace App\States\Concrete;

use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;

class IdleState implements VendingMachineState
{

    const DISPLAY_MESSAGE = 'Please insert coins.';
    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
    }

    public function insertCoin($coin)
    {
        $this->machine->userMoneyManager->insertCoin($coin);
        $this->machine->setState($this->machine->hasMoneyState);
    }

    public function returnCoins()
    {
        return $this->machine->userMoneyManager->returnCoins();
    }

    public function selectItem($itemCode)
    {
        throw new \Exception("Please insert coins first.");
    }

    public function service($action, $parameters = [])
    {
        return $this->machine->service($action, $parameters);
    }
}
