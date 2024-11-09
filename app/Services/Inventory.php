<?php

namespace App\Services;

use App\Models\Item;

class Inventory
{
    public $items = [];
    public $coins = [];

    public function __construct()
    {
        $this->items = [
            50 => ['item' => new Item('Water', 0.65), 'count' => 5],  // @todo code and count array? or make class?, move this init to service action
            55 => ['item' => new Item('Juice', 1.00), 'count' => 5],
            60  => ['item' => new Item('Soda', 1.50), 'count' => 5],
        ];

        $this->coins = [
            1.00 => 10, // @todo make value class for coin?
            0.25 => 10,
            0.10 => 10,
            0.05 => 10,
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
            $this->coins[$coin->value]++;
        }
    }

    public function getChange($amount)
    {
        $change = [];
        $remaining = $amount;

        $coinsConsidered = array_keys($this->coins);
        // unset($coinsForChange[1.00]); //@todo we do not give back 1 euro coins? read test descripotion again

        foreach ($coinsConsidered as $coinValue) {

            $shouldAndCanReturnMoreChange = $remaining >= $coinValue && $this->coins[$coinValue] > 0;
            while ($shouldAndCanReturnMoreChange) {
//                @todo we need a stronger algorithm for this maybe extract? what is gives back too much, what if is greedy hillclimbing stuck and should instead move on to smaller coins!!!
//                $change[] = $coinValue;
//                $remaining -= $coinValue;
//                $this->coins[$coinValue]--;
            }
        }

        if ($remaining == 0) {
            return $change;
        } else {
            // @todo not enough change, eturn coins back to inventory ?
//            foreach ($change as $coin) {
//                $this->coins[$coin->value]++;
//            }
            return null;
        }
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
