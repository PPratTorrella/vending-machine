<?php

namespace App\States\Concrete;

use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;

class IdleState implements VendingMachineState
{
    const DISPLAY_MESSAGE = 'Please insert coins.';
    const SELECTED_ITEM_MESSAGE = "Please insert coins before selecting an item.";
    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
        $this->machine->displayMessage = self::DISPLAY_MESSAGE;
    }

    public function insertCoin($coin)
    { //@todo al these in all states to commands
        $this->machine->userMoneyManager->insertCoin($coin);
        $this->machine->setHasMoneyState();
    }

    public function returnCoins()
    {
        return $this->machine->userMoneyManager->returnCoins();
    }

    public function selectItem($itemCode)
    {
        $this->machine->setDisplayMessage(self::SELECTED_ITEM_MESSAGE);

        $command = new SelectItemCommand($this->machine, $itemCode, allowed: false);
        return $command->execute();
    }
}
