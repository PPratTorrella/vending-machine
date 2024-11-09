<?php

namespace Models;

use App\Models\VendingMachine;
use PHPUnit\Framework\TestCase;

class vendingMachineTest extends TestCase
{

    public function test_vending_machine_gets_item_ok()
    {
        $vendingMachine = new VendingMachine();

        $vendingMachine->insertCoin(100);
        $vendingMachine->insertCoin(25);

        $coins = $vendingMachine->getInsertedCoins();
        $this->assertEquals([100, 25], $coins);

        $return = $vendingMachine->selectItem(50); // @todo init with service some water to code 50
        $this->assertEquals('Water', $return['item']->name); // @todo class for items? inside a item manager class? Inventory

        $this->assertEquals([25, 25, 10], $return['change'], 'Should return optimal combination for 60 cents');

        $askReturnAGain = $vendingMachine->returnCoins();
        $this->assertEmpty($askReturnAGain);
    }

    public function test_vending_machine_returns_money_ok()
    {
        $vendingMachine = new VendingMachine();

        $vendingMachine->insertCoin(1);
        $coins = $vendingMachine->getInsertedCoins();
        $this->assertContains(1, $coins);

        $vendingMachine->insertCoin(0.25);
        $coins = $vendingMachine->getInsertedCoins();
        $this->assertContains(0.25, $coins);

        $this->assertEquals([1, 0.25], $coins);

        $returnedCoins = $vendingMachine->returnCoins();

        foreach ($returnedCoins as $returnedCoin) {
            $this->assertContains($returnedCoin, [1, 0.25]);
        }

        $this->assertEquals(1.25, array_sum($coins));

        $askReturnAGain = $vendingMachine->returnCoins();
        $this->assertEmpty($askReturnAGain);
    }

    public function test_vending_machine_idle_not_returns_money_ok()
    {
        $vendingMachine = new VendingMachine();
        $askReturnAGain = $vendingMachine->returnCoins();
        $this->assertEmpty($askReturnAGain);
    }
}
