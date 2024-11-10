<?php

namespace App\States\Concrete;

use App\Commands\Concrete\VendingMachine\InsertCoinCommand;
use App\Commands\Concrete\VendingMachine\ReturnCoinsCommand;
use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;

class HasMoneyState implements VendingMachineState
{
    const DISPLAY_MESSAGE = 'Insert more coins or select an item.';
    const SERVICE_MESSAGE = "Please wait for current transaction to finish.";

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

    public function service($items = [], $coins = []): void
    {
        // we could allow this, but as an example of State control we block.
        // Also, we don't care now about service attempt param data getting lost but depending on usage of api this might be an issue
        $this->machine->setDisplayMessage(self::SERVICE_MESSAGE);
    }
}
