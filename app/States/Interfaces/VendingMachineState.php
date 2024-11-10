<?php

namespace App\States\Interfaces;

interface VendingMachineState
{
    public function insertCoin($coin): void;

    public function returnCoins(): array;

    public function selectItem($itemCode): array;
}
