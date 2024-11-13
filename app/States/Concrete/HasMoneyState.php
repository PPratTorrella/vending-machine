<?php

namespace App\States\Concrete;

use App\Commands\Concrete\VendingMachine\InsertCoinCommand;
use App\Commands\Concrete\VendingMachine\ReturnCoinsCommand;
use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Factories\VendingMachineStateFactory;
use App\Models\VendingMachine;
use App\Presenters\VendingMachinePresenter;
use App\States\Interfaces\VendingMachineState;

class HasMoneyState implements VendingMachineState
{
    const DISPLAY_MESSAGE = 'Insert more coins or select an item.';
    const TOTAL_SUM_PREFIX = 'Total inserted: ';
    const SERVICE_MESSAGE = 'Service not allowed. Please wait for current transaction to finish.';
    const INSERTED_COIN_NOT_VALID = 'Returned invalid last coin.';
    const STATE_NAME = VendingMachineStateFactory::HAS_MONEY_STATE_NAME;

    private VendingMachine $machine;
    private VendingMachinePresenter $presenter;

    public function __construct($machine, VendingMachinePresenter $presenter)
    {
        $this->machine = $machine;
        $this->presenter = $presenter;
        $this->setMessage(null, true);
    }

    public function insertCoin($coin): array
    {
        $command = new InsertCoinCommand($this->machine, $coin);
        $result = $command->execute();
        $message = empty($result) ? null : self::INSERTED_COIN_NOT_VALID;
        $this->setMessage($message, true);
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
        $this->setMessage(self::SERVICE_MESSAGE, true);
        return false;
    }

    public function getName(): string
    {
        return self::STATE_NAME;
    }

    public function setMessage($message = null, $prefixDefault = false): void
    {
        if (empty($message)) {
            $message = self::DISPLAY_MESSAGE;
        }
        if ($prefixDefault) {
            $total = $this->machine->getInsertedCoinsTotal();
            $totalFormatted = $this->presenter->formatPrice($total);
            $message = self::TOTAL_SUM_PREFIX . $totalFormatted . '€. ' . $message;
        }
        $this->machine->setDisplayMessage($message);
    }
}
