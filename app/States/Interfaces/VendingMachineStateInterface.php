<?php

namespace App\States\Interfaces;

interface VendingMachineStateInterface
{
    public function insertCoin($coin): array;

    public function returnCoins(): array;

    public function selectItem($itemCode): array;

    public function service(array $items, array $coins): bool;

    public function punch(): void;

    public function getName(): string;

    public function setMessage($message, $prefixDefault = false): void;
}
