<?php

namespace App\States\Concrete;

use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;
use Exception;

class HasMoneyState implements VendingMachineState
{
    const DISPLAY_MESSAGE = 'Insert more coins or select an item.';
    const INSUFFICIENT_FUNDS_MESSAGE = "Insufficient funds. Please insert more coins.";
    const ERROR_MESSAGE = 'ERROR occured. Transaction cancelled, try again.';

    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
        $this->machine->displayMessage = self::DISPLAY_MESSAGE;
    }

    public function insertCoin($coin): void
    {
        $this->machine->userMoneyManager->insertCoin($coin);
    }

    public function returnCoins(): array
    {
        return $this->machine->userMoneyManager->returnCoins();
    }

    /**
     * // @todo should def know way less, interact only with machine without knowing specifics, likewise elsewhere!
     * @throws Exception
     */
    public function selectItem($itemCode): array
    {
        $item = $this->machine->inventory->showItem($itemCode);

        $totalInserted = $this->machine->userMoneyManager->getTotal();

        $outOfStock = !$this->machine->inventory->items[$itemCode]['count'];

        if ($outOfStock) {
            throw new Exception("Item out of stock.");
        }
        if ($totalInserted < $item->price) {
            throw new Exception(self::INSUFFICIENT_FUNDS_MESSAGE);
        }

        $changeAmount = $totalInserted - $item->getPrice();

        $changeCoins = $this->machine->inventory->calculateChange($changeAmount);

        if ($changeCoins === null) {
            throw new Exception("Cannot dispense change. Transaction cancelled.");
        }

        $originalItemCount = $this->machine->inventory->items[$itemCode]['count'];
        $originalInventoryCoins = $this->machine->inventory->coins;
        $originalUserCoins = $this->machine->userMoneyManager->insertedCoins;

        try {
            $this->machine->inventory->decrementItem($itemCode);
            $this->machine->inventory->addCoins($this->machine->userMoneyManager->insertedCoins);
            $this->machine->inventory->removeCoins($changeCoins);
            $this->machine->userMoneyManager->reset();

        } catch (Exception $e) {
            $this->machine->inventory->items[$itemCode]['count'] = $originalItemCount;
            $this->machine->inventory->coins = $originalInventoryCoins;
            $this->machine->userMoneyManager->insertedCoins = $originalUserCoins;
            throw new Exception(self::ERROR_MESSAGE); // @todo use custom exception classes for controlled messages
        }

        $this->machine->setState($this->machine->idleState);

        return [
            'item'  => $item,
            'change' => $changeCoins,
        ];
    }
}
