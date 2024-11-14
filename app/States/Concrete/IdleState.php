<?php

namespace App\States\Concrete;

use App\Commands\Concrete\VendingMachine\InsertCoinCommand;
use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Commands\Concrete\VendingMachine\ServiceCommand;
use App\Factories\VendingMachineStateFactory;
use App\Engine\VendingMachine;
use App\States\Interfaces\VendingMachineStateInterface;

class IdleState implements VendingMachineStateInterface
{
    const DISPLAY_MESSAGE = 'Please insert coins.';
    const SELECTED_ITEM_MESSAGE = 'Please insert coins before selecting an item.';
    const RETURN_COINS_MESSAGE = 'No coins to return.';
    const INSERTED_COIN_NOT_VALID = 'Returned invalid coin.';
    const STATE_NAME = VendingMachineStateFactory::IDLE_STATE_NAME;

    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
        $this->setMessage(null, true);
    }

    public function insertCoin($coin): array
    {
        $command = new InsertCoinCommand($this->machine, $coin);
        $result = $command->execute();
        if (empty($result)) {
            $this->machine->setHasMoneyState();
        } else {
            $this->setMessage(self::INSERTED_COIN_NOT_VALID, true);
        }
        return $result;
    }

    public function returnCoins(): array
    {
        // for extra safety could still call command, but in this project we'll trust states
        $this->setMessage(self::RETURN_COINS_MESSAGE);
        return [];
    }

    public function selectItem($itemCode): array
    {
        $this->setMessage(self::SELECTED_ITEM_MESSAGE);

        $command = new SelectItemCommand($this->machine, $itemCode, allowed: false);
        return $command->execute();
    }

    public function service($items = [], $coins = []): bool
    {
        $command = new ServiceCommand($this->machine, $items, $coins);
        $command->execute();
        return true;
    }

    public function getName(): string
    {
        return self::STATE_NAME;
    }

    public function setMessage($message = null, $prefixDefault = false): void
    {
        if ($prefixDefault) {
            $message = self::DISPLAY_MESSAGE . $message;
        }
        $this->machine->setDisplayMessage($message);
    }
}
