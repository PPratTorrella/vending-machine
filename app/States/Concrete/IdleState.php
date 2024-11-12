<?php

namespace App\States\Concrete;

use App\Commands\Concrete\VendingMachine\InsertCoinCommand;
use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Commands\Concrete\VendingMachine\ServiceCommand;
use App\Factories\VendingMachineStateFactory;
use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;

class IdleState implements VendingMachineState
{
    const DISPLAY_MESSAGE = 'Please insert coins.';
    const SELECTED_ITEM_MESSAGE = 'Please insert coins before selecting an item.';
    const RETURN_COINS_MESSAGE = 'No coins to return.';
    const STATE_NAME = VendingMachineStateFactory::IDLE_STATE_NAME;

    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
        $this->machine->setDisplayMessage(self::DISPLAY_MESSAGE);
    }

    public function insertCoin($coin): void
    {
        $command = new InsertCoinCommand($this->machine, $coin);
        $command->execute();

        $this->machine->setHasMoneyState();
    }

    public function returnCoins(): array
    {
        // for extra safety could still call command, but in this project we'll trust states
        $this->machine->setDisplayMessage(self::RETURN_COINS_MESSAGE);
        return [];
    }

    public function selectItem($itemCode): array
    {
        $this->machine->setDisplayMessage(self::SELECTED_ITEM_MESSAGE);

        $command = new SelectItemCommand($this->machine, $itemCode, allowed: false);
        return $command->execute();
    }

    public function service($items = [], $coins = []): void
    {
        $command = new ServiceCommand($this->machine, $items, $coins);
        $command->execute();
    }

    public function getName(): string
    {
        return self::STATE_NAME;
    }
}
