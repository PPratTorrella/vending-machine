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
    }

    /**
     * @param int $code
     * @return Item|null
     */
    public function showItem($code): ?Item
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
            if (!isset($this->coins[$coin])) {
                $this->coins[$coin] = 0;
            }
            $this->coins[$coin]++;
        }
    }

    public function removeCoins($coins)
    {
        foreach ($coins as $coin) {
            if (!isset($this->coins[$coin])) {
                $this->coins[$coin] = 0;
            }

            $this->coins[$coin]--;

            if ($this->coins[$coin] < 0) {
                //@todo this is problematic
                $this->coins[$coin] = 0;
            }
        }
    }

    /**
     * Doesn't persist changes, only calculates them
     * @param $amount
     * @return array|null
     */
    public function calculateChange($amount): ?array
    {
        return $this->changeCalculator->calculateOptimalChange($amount, $this->coins);
    }

    /**
     * Overrides inventory items and coins with the provided values
     * If an item code or coin is not provided but is currently available, it will be kept as is
     *
     * @param array $items format: [code => ['name' => 'ItemName', 'count' => X, 'price' => Y]]
     * @param array $coins
     */
    public function updateInventory(array $items = [], array $coins = [])
    {
        foreach ($items as $code => $data) {
            $this->items[$code] = [
                'item' => new Item($data['name'], $data['price']),
                'count' => $data['count'],
            ];
        }
        foreach ($coins as $value => $count) {
            //@todo validate that $value is a valid coin here and elsewhere
            $this->coins[$value] = $count;
        }
    }
}
