<?php

namespace App\Presenters;

class VendingMachinePresenter
{
    protected array $result;

    public function __construct(array $result)
    {
        $this->result = $result;
    }

    public function getItem(): ?string
    {
        if (isset($this->result['item'])) {
            $itemName = $this->result['item']->getName();
            $price = $this->formatPrice($this->result['item']->getPrice());
            return "$itemName - $price";
        }

        return null;
    }

    public function getChange(): array
    {
        if (!isset($this->result['change'])) {
            return [];
        }

        $coinCounts = array_count_values($this->result['change']);

        return array_map(function ($coinValue, $count) {
            return "$count x " . $this->formatPrice($coinValue);
        }, array_keys($coinCounts), $coinCounts);
    }

    public function toArray(): array
    {
        return [
            'item' => $this->getItem(),
            'change' => $this->getChange(),
        ];
    }

    public function formatPrice(int $cents): string
    {
        return '€' . number_format($cents / 100, 2);
    }
}
