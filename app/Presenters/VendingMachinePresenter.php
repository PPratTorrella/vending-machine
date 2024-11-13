<?php

namespace App\Presenters;

use App\Models\Item;

class VendingMachinePresenter
{
    protected Item $item;
    private array $coins = [];

    public function getItem(): ?string
    {
        if (!isset($this->item)) {
            return null;
        }
        $itemName = $this->item->getName();
        $price = $this->formatPrice($this->item->getPrice());
        return "$itemName - $price";
    }

    public function getCoins(): array
    {
        if (empty($this->coins)) {
            return [];
        }

        $coinCounts = array_count_values($this->coins);

        return array_map(function ($coinValue, $count) {
            return "$count x " . $this->formatPrice($coinValue);
        }, array_keys($coinCounts), $coinCounts);
    }

    public function formatPrice(int $cents): string
    {
        return '€' . number_format($cents / 100, 2);
    }

    public function formatInsertedCoins(array $insertedCoins): string
    {
        return implode(', ', array_map(fn($coin) => $this->formatPrice($coin), $insertedCoins));
    }

    public function setItem(Item $item)
    {
        $this->item = $item;
    }

    public function setCoins(array $coins)
    {
        $this->coins = $coins;
    }
}
