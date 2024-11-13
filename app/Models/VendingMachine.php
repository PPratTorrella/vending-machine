<?php

namespace App\Models;

use App\Models\Interfaces\VendingMachineInterface;
use App\Services\Inventory;
use App\Services\UserMoneyManager;
use App\States\Concrete\HasMoneyState;
use App\States\Concrete\IdleState;
use App\States\Interfaces\VendingMachineStateInterface;
use Illuminate\Support\Facades\App;

class VendingMachine implements VendingMachineInterface
{
    const ERROR_MESSAGE_SELECT_ITEM = 'ERROR occured. Transaction cancelled, try again.';
    const ERROR_MESSAGE_INSUFFICIENT_FUNDS = 'Insufficient funds. Please insert more coins.';
    const ERROR_MESSAGE_NOT_ENOUGH_CHANGE = 'Not enough change. Transaction cancelled.';
    const ERROR_MESSAGE_OUT_OF_STOCK = 'Item out of stock.';
    const ERROR_MESSAGE_CODE_NOT_SET = 'Invalid code.';
    const ERROR_MESSAGE_INVALID_COIN = 'Invalid coin.';

    public VendingMachineStateInterface $state;
    public Inventory $inventory;
    public UserMoneyManager $userMoneyManager;
    public string $displayMessage;

    public function __construct()
    {
        $this->inventory = app(Inventory::class);
        $this->setIdleState();
        $this->userMoneyManager = new UserMoneyManager();
    }

    public function insertCoin($coin): array
    {
        return $this->state->insertCoin($coin);
    }

    public function returnCoins(): array
    {
        return $this->state->returnCoins();
    }

    public function selectItem($itemCode): array
    {
        return $this->state->selectItem($itemCode);
    }

    public function service($items = [], $coins = []): bool
    {
        return $this->state->service($items, $coins);
    }

    public function getInsertedCoins(): array
    {
        return $this->userMoneyManager->getInsertedCoins();
    }

    public function getInsertedCoinsTotal(): int
    {
        return $this->userMoneyManager->getTotal();
    }

    public function getInventory(): array
    {
        // @todo could make some class (DTO)
        return [
            'items' => $this->inventory->items,
            'coins' => $this->inventory->coins,
        ];
    }

    public function updateInventory($items, $coins): void
    {
        $this->inventory->updateInventory($items, $coins);
    }

    public function codeExists($itemCode): bool
    {
        return isset($this->inventory->items[$itemCode]);
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

    public function setHasMoneyState(): void
    {
        $this->state = App::make(HasMoneyState::class, ['machine' => $this]);
    }

    public function setState(VendingMachineStateInterface $state): void
    {
        $this->state = $state;
    }

}
