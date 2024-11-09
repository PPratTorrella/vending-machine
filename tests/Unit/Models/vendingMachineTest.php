<?php

namespace Models;

use App\Models\VendingMachine;
use App\States\Concrete\HasMoneyState;
use App\States\Concrete\IdleState;
use PHPUnit\Framework\TestCase;

class vendingMachineTest extends TestCase
{

    public function test_vending_machine_gets_item_ok()
    {
        $vendingMachine = new VendingMachine();

        $vendingMachine->insertCoin(100);
        $this->assertInstanceOf(HasMoneyState::class, $vendingMachine->state);
        $vendingMachine->insertCoin(25);

        $coins = $vendingMachine->getInsertedCoins();
        $this->assertEquals([100, 25], $coins);

        $return = $vendingMachine->selectItem(50); // @todo init with service some water to code 50
        $this->assertEquals('Water', $return['item']->name);

        $this->assertEquals([25, 25, 10], $return['change'], 'Should return optimal combination for 60 cents');

        $askReturnAGain = $vendingMachine->returnCoins();
        $this->assertEmpty($askReturnAGain);

        $this->assertInstanceOf(IdleState::class, $vendingMachine->state);
    }

    public function test_vending_machine_returns_money_ok()
    {
        $vendingMachine = new VendingMachine();

        $vendingMachine->insertCoin(100);
        $coins = $vendingMachine->getInsertedCoins();
        $this->assertContains(100, $coins);

        $vendingMachine->insertCoin(25);
        $coins = $vendingMachine->getInsertedCoins();
        $this->assertContains(25, $coins);

        $this->assertEquals([100, 25], $coins);

        $returnedCoins = $vendingMachine->returnCoins();

        foreach ($returnedCoins as $returnedCoin) {
            $this->assertContains($returnedCoin, [100, 25]);
        }

        $this->assertEquals(125, array_sum($coins));

        $askReturnAGain = $vendingMachine->returnCoins();
        $this->assertEmpty($askReturnAGain);

        $this->assertInstanceOf(IdleState::class, $vendingMachine->state);
    }

    public function test_vending_machine_idle_not_returns_money_ok()
    {
        $vendingMachine = new VendingMachine();
        $this->assertInstanceOf(IdleState::class, $vendingMachine->state);
        $askReturnAGain = $vendingMachine->returnCoins();
        $this->assertEmpty($askReturnAGain);
    }
}
