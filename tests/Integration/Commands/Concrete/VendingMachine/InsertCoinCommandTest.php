<?php

namespace Tests\Integration\Commands\Concrete\VendingMachine;

use App\Commands\Concrete\VendingMachine\InsertCoinCommand;
use App\Engine\VendingMachine;
use Tests\TestCase;

class InsertCoinCommandTest extends TestCase
{
    public function testExecuteInsertsValidCoin()
    {
        $vendingMachine = app(VendingMachine::class);

        $validCoin = 100;
        $command = new InsertCoinCommand($vendingMachine, $validCoin);
        $command->execute();

        $this->assertContains($validCoin, $vendingMachine->getInsertedCoins());
        $this->assertEquals($validCoin, $vendingMachine->getInsertedCoinsTotal());
    }

    public function testExecuteWithInvalidCoin()
    {
        $vendingMachine = app(VendingMachine::class);

        $invalidCoin = 3;
        $command = new InsertCoinCommand($vendingMachine, $invalidCoin);
        $command->execute();

        $this->assertNotContains($invalidCoin, $vendingMachine->getInsertedCoins());
        $this->assertEquals(0, $vendingMachine->getInsertedCoinsTotal());
    }
}
