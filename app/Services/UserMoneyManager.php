<?php

namespace App\Services;

class UserMoneyManager
{
    public array $insertedCoins = [];

    public function insertCoin($coin): void
    {
        $this->insertedCoins[] = $coin;
    }

    public function returnCoins(): array
    {
        $coins = $this->insertedCoins;
        $this->insertedCoins = [];
        return $coins;
    }

    public function getInsertedCoins(): array
    {
        return $this->insertedCoins;
    }

    public function getTotal(): int
    {
        return array_sum($this->insertedCoins);
    }

    public function reset(): void
    {
        $this->insertedCoins = [];
    }
}
