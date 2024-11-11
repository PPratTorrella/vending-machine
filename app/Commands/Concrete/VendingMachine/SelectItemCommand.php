<?php

namespace App\Commands\Concrete\VendingMachine;

use App\Commands\Interfaces\Command;
use App\Models\VendingMachine;
use Exception;

class SelectItemCommand implements Command
{
    private VendingMachine $machine;
    private $itemCode;
    private $allowed;

    public function __construct(VendingMachine $machine, $itemCode, $allowed = true)
    {
        $this->machine = $machine;
        $this->itemCode = $itemCode;
        $this->allowed = $allowed;
    }

    public function execute(): array
    {
        $result = [
            'item'  => null,
            'change' => [],
        ];

        if (!$this->allowed) {
            return $result;
        }

        if (!$this->machine->hasStock($this->itemCode)) {
            $this->machine->displayMessage = VendingMachine::ERROR_MESSAGE_OUT_OF_STOCK;
            return $result;
        }
        if (!$this->machine->hasFundsForItem($this->itemCode)) {
            $this->machine->displayMessage = VendingMachine::ERROR_MESSAGE_INSUFFICIENT_FUNDS;
            return $result;
        }
        if (!$this->machine->hasChange($this->itemCode)) {
            $this->machine->displayMessage = VendingMachine::ERROR_MESSAGE_NOT_ENOUGH_CHANGE;
            return $result;
        }

        $item = $this->machine->inventory->showItem($this->itemCode);
        $totalInserted = $this->machine->userMoneyManager->getTotal();
        $changeAmount = $totalInserted - $item->getPrice();
        $changeCoins = $this->machine->inventory->calculateChange($changeAmount);

        $originalItemCount = $this->machine->inventory->items[$this->itemCode]['count'];
        $originalInventoryCoins = $this->machine->inventory->coins;
        $originalUserCoins = $this->machine->userMoneyManager->insertedCoins;

        try {
            $this->machine->inventory->decrementItem($this->itemCode);
            $this->machine->inventory->addCoins($this->machine->userMoneyManager->insertedCoins);
            $this->machine->inventory->removeCoins($changeCoins);
            $this->machine->userMoneyManager->reset();

        } catch (Exception $e) {
            $this->machine->inventory->items[$this->itemCode]['count'] = $originalItemCount;
            $this->machine->inventory->coins = $originalInventoryCoins;
            $this->machine->userMoneyManager->insertedCoins = $originalUserCoins;
            $this->machine->displayMessage = VendingMachine::ERROR_MESSAGE_SELECT_ITEM;
            return $result;
        }

        $result['item'] = $item;
        $result['change'] = $changeCoins;

        return $result;
    }

}