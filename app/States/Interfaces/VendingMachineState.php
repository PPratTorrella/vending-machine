<?php

namespace App\States\Interfaces;

interface VendingMachineState
{
    public function insertCoin($coin): array;

    public function returnCoins(): array;

    public function selectItem($itemCode): array;

    public function service(array $items, array $coins): bool;

    public function getName(): string;
}
