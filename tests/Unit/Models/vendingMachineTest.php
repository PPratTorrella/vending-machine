<?php

namespace Models;

use App\Models\VendingMachine;
use PHPUnit\Framework\TestCase;

class vendingMachineTest extends TestCase
{

    public function test_vending_machine_gets_item_ok()
    {
        $vendingMachine = new VendingMachine();
        $vendingMachine->insertCoin(1.00);
        $vendingMachine->insertCoin(0.25);
        $item = $vendingMachine->selectItem('Water');
        $this->assertEquals('Water', $item); // @todo class for items? inside a item manager class? Inventory
//        $change = $vendingMachine->returnCoins(); // @todo returns automatically together with Item?
    }

    public function test_vending_machine_returns_money_ok()
    {
        $vendingMachine = new VendingMachine();

        $vendingMachine->insertCoin(1);
        $coins = $vendingMachine->getInsertedCoins();
        $this->assertContains(1, $coins);

        $vendingMachine->insertCoin(0.25); // assert: changed states but still same machine instance?
        $coins = $vendingMachine->getInsertedCoins();
        $this->assertContains(0.25, $coins);

        $this->assertEquals([1, 0.25], $coins);

        $returnedCoins = $vendingMachine->returnCoins();

        foreach ($returnedCoins as $returnedCoin) {
            $this->assertContains($returnedCoin, [1, 0.25]);
        }

        $this->assertEquals(1.25, array_sum($coins));
    }
}
