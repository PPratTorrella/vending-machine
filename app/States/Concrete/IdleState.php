<?php

namespace App\States\Concrete;

use App\States\Interfaces\VendingMachineState;

class IdleState implements VendingMachineState
{

    public function insertCoin($coin)
    {
    }

    public function returnCoins()
    {
    }

    public function selectItem($itemCode)
    {
    }

    public function service($serviceCommand)
    {
    }
}
