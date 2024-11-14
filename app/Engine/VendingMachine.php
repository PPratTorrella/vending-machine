<?php

namespace App\Engine;

use App\Factories\VendingMachineStateFactory;
use App\Engine\Interfaces\VendingMachineInterface;
use App\Services\Inventory;
use App\Services\UserMoneyManager;
use App\States\Interfaces\VendingMachineStateInterface;

class VendingMachine implements VendingMachineInterface
{
    const ERROR_MESSAGE_SELECT_ITEM = 'ERROR occured. Transaction cancelled, try again.';
    const ERROR_MESSAGE_INSUFFICIENT_FUNDS = 'Insufficient funds. Please insert more coins.';
    const ERROR_MESSAGE_NOT_ENOUGH_CHANGE = 'Not enough change. Transaction cancelled.';
    const ERROR_MESSAGE_OUT_OF_STOCK = 'Item out of stock.';
    const ERROR_MESSAGE_CODE_NOT_SET = 'Invalid code.';

    public VendingMachineStateInterface $state;
    public Inventory $inventory;
    public UserMoneyManager $userMoneyManager;
    private string $displayMessage;
    private VendingMachineStateFactory $stateFactory;

    public function __construct(VendingMachineStateFactory $stateFactory, Inventory $inventory, UserMoneyManager $userMoneyManager)
    {
        $this->inventory = $inventory;
        $this->stateFactory = $stateFactory;
        $this->userMoneyManager = $userMoneyManager;
        $this->setIdleState();
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

    public function punch(): void
    {
        $this->state->punch();
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
        $this->state = $this->stateFactory->create(VendingMachineStateFactory::IDLE_STATE_NAME, $this);
    }

    public function setHasMoneyState(): void
    {
        $this->state = $this->stateFactory->create(VendingMachineStateFactory::HAS_MONEY_STATE_NAME, $this);
    }

    public function setBrokenState(): void
    {
        $this->state = $this->stateFactory->create(VendingMachineStateFactory::BROKEN_STATE_NAME, $this);
    }

    public function setState(VendingMachineStateInterface $state): void
    {
        $this->state = $state;
    }

    public function getStateName()
    {
        return $this->state->getName();
    }
}
