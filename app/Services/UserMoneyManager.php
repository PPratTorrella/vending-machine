<?php

namespace App\Services;

class UserMoneyManager
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

    public function getTotal()
    {
        return array_sum($this->insertedCoins);
    }
}
