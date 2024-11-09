<?php

namespace App\Services;

use App\Helpers\ChangeCalculatorHelper;
use App\Models\Item;

class Inventory
{
    public $items = [];
    public $coins = [];
    private ChangeCalculatorHelper $changeCalculator;

    public function __construct(ChangeCalculatorHelper $changeCalculator)
    {
        $this->changeCalculator = $changeCalculator;
        $this->initializeInventory();
    }

    private function initializeInventory()
    {
        $this->items = [
            50 => ['item' => new Item('Water', 65), 'count' => 5],
            55 => ['item' => new Item('Juice', 100), 'count' => 5],
            60 => ['item' => new Item('Soda', 150), 'count' => 5],
        ];

        $this->coins = [
            100 => 10,
            25 => 10,
            10 => 10,
            5 => 10,
        ];
    }

    /**
     * @param int $code
     * @return Item|null
     */
    public function showItem($code)
    {
        return $this->items[$code]['item'] ?? null;
    }

    public function decrementItem($code)
    {
        if ($this->items[$code]['count'] > 0) {
            $this->items[$code]['count']--;
            return true;
        }
        return false;
    }

    public function addCoins($coins)
    {
        foreach ($coins as $coin) {
            $this->coins[$coin]++;
        }
    }

    public function getChange($amount)
    {
        $result = $this->changeCalculator->calculateOptimalChange($amount, $this->coins);

        if ($result === null) {
            return null;
        }

        // update inventory only if we found a valid solution
        foreach ($result as $coinValue) {
            $this->coins[$coinValue]--;
        }

        return $result;
    }

    public function setItemCount($code, $count)
    {
        if (isset($this->items[$code])) {
            $this->items[$code]['count'] = $count;
        }
    }

    public function setCoinCount($value, $count)
    {
        $this->coins[$value] = $count;
    }
}
