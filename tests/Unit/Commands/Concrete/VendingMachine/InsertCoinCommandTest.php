<?php

namespace Tests\Unit\Commands\Concrete\VendingMachine;

use App\Commands\Concrete\VendingMachine\InsertCoinCommand;
use App\Models\VendingMachine;
use Tests\TestCase;

class InsertCoinCommandTest extends TestCase
{
    public function testExecuteInsertsValidCoin()
    {
        $vendingMachine = new VendingMachine();

        $validCoin = 100;
        $command = new InsertCoinCommand($vendingMachine, $validCoin);
        $command->execute();

        $this->assertContains($validCoin, $vendingMachine->getInsertedCoins());
        $this->assertEquals($validCoin, $vendingMachine->getInsertedCoinsTotal());
    }

    public function testExecuteWithInvalidCoin()
    {
        $this->markTestSkipped('Need to start validate coins');

        $vendingMachine = new VendingMachine();

        $invalidCoin = 3;
        $command = new InsertCoinCommand($vendingMachine, $invalidCoin);
        $command->execute();

        $this->assertNotContains($invalidCoin, $vendingMachine->getInsertedCoins());
        $this->assertEquals(0, $vendingMachine->getInsertedCoinsTotal());
        $this->assertStringContainsString(VendingMachine::ERROR_MESSAGE_INVALID_COIN, $vendingMachine->displayMessage);

        //@todo needs to dispense this coin out of the machine?
    }
}
