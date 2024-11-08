<?php

namespace App\Services;

class MoneyManager
{
    public array $insertedCoins = [];

    public function insertCoin($coin)
    {
        $this->insertedCoins[] = $coin;
    }

    public function returnCoins()
    {
        $coins = $this->insertedCoins;
        $this->insertedCoins = [];
        return $coins;
    }

    public function getInsertedCoins()
    {
        return $this->insertedCoins;
    }
}
