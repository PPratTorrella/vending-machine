<?php

namespace App\States\Concrete;

use App\Commands\Concrete\VendingMachine\ReturnCoinsCommand;
use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;

class HasMoneyState implements VendingMachineState //@todo move all states to parent Vendingmachine folder as its specifically for this interface?
{
    const DISPLAY_MESSAGE = 'Insert more coins or select an item.';

    private VendingMachine $machine; // @todo depend on interface

    public function __construct($machine)
    {
        $this->machine = $machine;
        $this->machine->setDisplayMessage(self::DISPLAY_MESSAGE);
    }

    public function insertCoin($coin): void
    {
        $this->machine->userMoneyManager->insertCoin($coin);
    }

    public function returnCoins(): array
    {
        $command = new ReturnCoinsCommand($this->machine);
        $result = $command->execute();

        $this->machine->setIdleState();

        return $result;
    }

    public function selectItem($itemCode): array
    {
        $command = new SelectItemCommand($this->machine, $itemCode);
        $result = $command->execute();

        if (!empty($result['item'])) {
            $this->machine->setIdleState();
        }

        return $result;
    }
}
