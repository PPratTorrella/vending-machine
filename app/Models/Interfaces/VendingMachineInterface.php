<?php

namespace App\Models\Interfaces;

interface VendingMachineInterface
{
    public function insertCoin($coin): array;

    public function returnCoins(): array;

    public function selectItem($itemCode): array;

    public function service(array $items, array $coins): bool;
}
