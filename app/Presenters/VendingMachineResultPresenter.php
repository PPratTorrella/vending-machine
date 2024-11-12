<?php

namespace App\Presenters;

class VendingMachineResultPresenter
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
            $price = number_format($this->result['item']->getPrice() / 100, 2);
            return "$itemName - €$price";
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
            return "$count x €" . number_format($coinValue / 100, 2);
        }, array_keys($coinCounts), $coinCounts);
    }

    public function toArray(): array
    {
        return [
            'item' => $this->getItem(),
            'change' => $this->getChange(),
        ];
    }
}
