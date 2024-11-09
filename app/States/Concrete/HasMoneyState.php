<?php

namespace App\States\Concrete;

use App\Factories\ItemFactory;
use App\Models\VendingMachine;
use App\States\Interfaces\VendingMachineState;
use Exception;

class HasMoneyState implements VendingMachineState
{
    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
    }

    public function insertCoin($coin)
    {
        $this->machine->userMoneyManager->insertCoin($coin);
    }

    public function returnCoins()
    {
        return $this->machine->userMoneyManager->returnCoins();
    }

    public function selectItem($itemCode)
    {
        $item = $this->machine->inventory->showItem($itemCode);
        $totalInserted = $this->machine->userMoneyManager->getTotal();

        $outOfStock = !$this->machine->inventory->items[$itemCode]['count']; // @todo could ask machine for this without knowing specifics, likewise elsewhere
        if ($outOfStock) {
            throw new Exception("Item out of stock.");
        }
        if ($totalInserted < $item->price) {
            throw new Exception("Insufficient funds. Please insert more coins.");
        }

        $changeAmount = $totalInserted - $item->getPrice();
        $changeCoins = $this->machine->inventory->getChange($changeAmount);

        if ($changeCoins === null) {
            throw new Exception("Cannot dispense change. Transaction cancelled.");
        }

        // Dispense item and change
        $this->machine->inventory->decrementItem($itemCode);
        $this->machine->inventory->addCoins($this->machine->userMoneyManager->insertedCoins);
        $this->machine->userMoneyManager->reset();
        $this->machine->setState($this->machine->idleState);

        return [
            'item'  => $item,
            'change' => $changeCoins,
        ];
    }

    public function service($action, $parameters = [])
    {
    }
}
