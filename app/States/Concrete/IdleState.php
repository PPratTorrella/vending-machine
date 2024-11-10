<?php

namespace App\States\Concrete;

use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;
use Exception;

class IdleState implements VendingMachineState
{
    const DISPLAY_MESSAGE = 'Please insert coins.';
    const SELECTED_ITEM_MESSAGE = "Please insert coins before selecting item.";
    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
        $this->machine->displayMessage = self::DISPLAY_MESSAGE;
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
        throw new Exception(self::SELECTED_ITEM_MESSAGE);
    }
}
