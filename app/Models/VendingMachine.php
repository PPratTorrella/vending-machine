<?php

namespace App\Models;

use App\Services\Inventory;
use App\Services\UserMoneyManager;
use App\States\Concrete\HasMoneyState;
use App\States\Concrete\IdleState;
use App\States\Interfaces\VendingMachineState;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendingMachine
{
    use HasFactory;

    const ERROR_MESSAGE_SELECT_ITEM = 'ERROR occured. Transaction cancelled, try again.';
    const ERROR_MESSAGE_INSUFFICIENT_FUNDS = "Insufficient funds. Please insert more coins.";
    const ERROR_MESSAGE_NOT_ENOUGH_CHANGE = "Not enough change. Transaction cancelled.";
    const ERROR_MESSAGE_OUT_OF_STOCK = "Item out of stock.";

    public HasMoneyState $hasMoneyState;
    public IdleState $idleState;
    public VendingMachineState $state;
    public Inventory $inventory;
    public UserMoneyManager $userMoneyManager;
    public string $displayMessage;


    public function __construct()
    {
        $this->inventory = app(Inventory::class);
        $this->idleState = new IdleState($this);
        $this->hasMoneyState = new HasMoneyState($this);
        $this->state = $this->idleState;
        $this->displayMessage = IdleState::DISPLAY_MESSAGE;
        $this->userMoneyManager = new UserMoneyManager();
    }

    public function insertCoin($value)
    {
        return $this->state->insertCoin($value);
    }

    public function returnCoins()
    {
        return $this->state->returnCoins();
    }

    public function selectItem($itemCode)
    {
        return $this->state->selectItem($itemCode);
    }

    public function service($items = [], $coins = [])
    {
        $this->inventory->updateInventory($items, $coins);
    }

    public function getInsertedCoins()
    {
        return $this->userMoneyManager->getInsertedCoins();
    }

    // @todo could make some class (DTO)
    public function getInventory(): array
    {
        return [
            'items' => $this->inventory->items,
            'coins' => $this->inventory->coins,
        ];
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
        $changeCoins = $this->inventory->calculateChange($changeAmount);
        return !empty($changeCoins);
    }

    public function setDisplayMessage(string $message)
    {
        $this->displayMessage = $message;
    }

    public function setIdleState()
    {
        $this->state = $this->idleState;
    }

    public function setHasMoneyState()
    {
        $this->state = $this->hasMoneyState;
    }

}
