<?php

namespace App\States\Interfaces;

interface VendingMachineState
{
    public function insertCoin($coin);

    public function returnCoins();

    public function selectItem($itemCode);

    public function service($serviceCommand); //@todo what needs to go in exactly?
}
