<?php

namespace App\States\Concrete;

use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;

class IdleState implements VendingMachineState
{
    const DISPLAY_MESSAGE = 'Please insert coins.';
    const SELECTED_ITEM_MESSAGE = "Please insert coins before selecting an item.";
    const RETURN_COINS_MESSAGE = "No coins to return.";
    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
        $this->machine->setDisplayMessage(self::DISPLAY_MESSAGE);
    }

    public function insertCoin($coin)
    {
        $this->machine->userMoneyManager->insertCoin($coin);
        $this->machine->setHasMoneyState();
    }

    public function returnCoins()
    {
        // for extra safety could still call command, but in our test we trust states
        $this->machine->setDisplayMessage(self::RETURN_COINS_MESSAGE);
        return [];
    }

    public function selectItem($itemCode)
    {
        $this->machine->setDisplayMessage(self::SELECTED_ITEM_MESSAGE);

        $command = new SelectItemCommand($this->machine, $itemCode, allowed: false);
        return $command->execute();
    }
}
