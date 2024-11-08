<?php

namespace App\States\Concrete;

use App\States\Interfaces\VendingMachineState;

class HasMoneyState implements VendingMachineState
{
    private $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
    }

    public function insertCoin($coinValue)
    {
    }

    public function returnCoins()
    {
        return [];
    }

    public function selectItem($itemCode)
    {
    }

    public function service($serviceCommand)
    {
    }
}
