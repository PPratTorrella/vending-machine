<?php

namespace Models;

use App\Models\VendingMachine;
use PHPUnit\Framework\TestCase;

class vendingMachineTest extends TestCase
{

    public function test_vending_machine_ok()
    {
        $vendingMachine = new VendingMachine();
        $vendingMachine->insertCoin(1.00);
        $vendingMachine->insertCoin(0.25);
        $item = $vendingMachine->selectItem('Water');

        $this->assertEquals('Water', $item); // @todo class for items?
        $change = $vendingMachine->returnCoins(); // @todo returns automatically together with Item?
    }
}
