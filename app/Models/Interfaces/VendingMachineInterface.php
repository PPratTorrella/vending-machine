<?php

namespace App\Models\Interfaces;

use App\Services\Inventory;

interface VendingMachineInterface
{
    public function insertCoin($coin): array;

    public function returnCoins(): array;

    public function selectItem($itemCode): array;

    public function service(array $items, array $coins): bool;

    public function setDisplayMessage(string $message): void;

    public function getInsertedCoins(): array;

    public function getInsertedCoinsTotal(): int;

    public function getInventory(): array;
}
