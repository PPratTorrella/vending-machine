<?php

namespace App\Models;

use App\Services\Inventory;
use App\Services\UserMoneyManager;
use App\States\Concrete\HasMoneyState;
use App\States\Concrete\IdleState;
use App\States\Interfaces\VendingMachineState;

class VendingMachine
{
    const ERROR_MESSAGE_SELECT_ITEM = 'ERROR occured. Transaction cancelled, try again.';
    const ERROR_MESSAGE_INSUFFICIENT_FUNDS = 'Insufficient funds. Please insert more coins.';
    const ERROR_MESSAGE_NOT_ENOUGH_CHANGE = 'Not enough change. Transaction cancelled.';
    const ERROR_MESSAGE_OUT_OF_STOCK = 'Item out of stock.';

    public VendingMachineState $state;
    public Inventory $inventory;
    public UserMoneyManager $userMoneyManager;
    public string $displayMessage;

    public function __construct()
    {
        $this->inventory = app(Inventory::class);
        $this->setIdleState();
        $this->userMoneyManager = new UserMoneyManager();
    }

    public function insertCoin($value)
    {
        $this->state->insertCoin($value);
    }

    public function returnCoins()
    {
        return $this->state->returnCoins();
    }

    public function selectItem($itemCode)
    {
        return $this->state->selectItem($itemCode);
    }

    public function service($items = [], $coins = []): bool
    {
        return $this->state->service($items, $coins);
    }

    public function getInsertedCoins()
    {
        return $this->userMoneyManager->getInsertedCoins();
    }

    public function getInsertedCoinsTotal()
    {
        return $this->userMoneyManager->getTotal();
    }

    // @todo could make some class (DTO)
    public function getInventory(): array
    {
        return [
            'items' => $this->inventory->items,
            'coins' => $this->inventory->coins,
        ];
    }

    public function updateInventory($items, $coins): void
    {
        $this->inventory->updateInventory($items, $coins);
    }

    public function hasStock($itemCode): bool
    {
        return $this->inventory->items[$itemCode]['count'] > 0;
    }

    public function hasFundsForItem($itemCode): bool
    {
        $item = $this->inventory->showItem($itemCode);
        $totalInserted = $this->userMoneyManager->getTotal();
        return $totalInserted >= $item->price;
    }

    public function hasChange($itemCode): bool
    {
        $item = $this->inventory->showItem($itemCode);
        $totalInserted = $this->userMoneyManager->getTotal();
        $changeAmount = $totalInserted - $item->price;
        if ($changeAmount == 0) {
            return true;
        }
        $changeCoins = $this->inventory->calculateChange($changeAmount);
        return !empty($changeCoins);
    }

    public function setInsertedCoins($coins): void
    {
        $this->userMoneyManager->insertedCoins = $coins;
    }

    public function setDisplayMessage(string $message): void
    {
        $this->displayMessage = $message;
    }

    public function getDisplayMessage(): string
    {
        return $this->displayMessage;
    }

    public function setIdleState(): void
    {
        $this->state = new IdleState($this); // @todo see what to do with these
    }

    public function setState(VendingMachineState $state): void
    {
        $this->state = $state;
    }

    public function setHasMoneyState(): void
    {
        $this->state = new HasMoneyState($this);
    }

}
