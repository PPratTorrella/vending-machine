<?php

namespace App\States\Concrete;

use App\Commands\Concrete\VendingMachine\InsertCoinCommand;
use App\Commands\Concrete\VendingMachine\ReturnCoinsCommand;
use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Factories\VendingMachineStateFactory;
use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;

class HasMoneyState implements VendingMachineState
{
    const DISPLAY_MESSAGE = 'Insert more coins or select an item.';
    const TOTAL_SUM_PREFIX = 'Total inserted: ';
    const SERVICE_MESSAGE = 'Service not allowed. Please wait for current transaction to finish.';
    const STATE_NAME = VendingMachineStateFactory::HAS_MONEY_STATE_NAME;

    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
        $this->setMessage();
    }

    public function insertCoin($coin): array
    {
        $command = new InsertCoinCommand($this->machine, $coin);
        $result = $command->execute();
        $this->setMessage();
        return $result;
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

    public function service($items = [], $coins = []): bool
    {
        $this->machine->setDisplayMessage(self::SERVICE_MESSAGE);
        return false;
    }

    public function getName(): string
    {
        return self::STATE_NAME;
    }

    private function setMessage($info = null)
    {
        $total = $this->machine->getInsertedCoinsTotal();
        $totalFormatted = number_format($total / 100, 2);
        $message = self::TOTAL_SUM_PREFIX . $totalFormatted . '€. ' . ($info ?? self::DISPLAY_MESSAGE);
        $this->machine->setDisplayMessage($message);
    }
}
