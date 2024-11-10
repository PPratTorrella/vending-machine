<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\UserMoneyManager;

class UserMoneyManagerTest extends TestCase
{

    public function testInsertCoin()
    {
        $moneyManager = new UserMoneyManager();
        $moneyManager->insertCoin(0.25);
        $this->assertEquals([0.25], $moneyManager->getInsertedCoins());
        $moneyManager->insertCoin(0.10);
        $this->assertEquals([0.25, 0.10], $moneyManager->getInsertedCoins());
    }

    public function testGetTotal()
    {
        $moneyManager = new UserMoneyManager();
        $this->assertEquals(0, $moneyManager->getTotal());
        $moneyManager->insertCoin(0.25);
        $moneyManager->insertCoin(0.10);
        $this->assertEquals(0.35, $moneyManager->getTotal());
    }

    public function testReturnCoins()
    {
        $moneyManager = new UserMoneyManager();
        $moneyManager->insertCoin(0.25);
        $moneyManager->insertCoin(0.10);
        $returnedCoins = $moneyManager->returnCoins();
        $this->assertEquals([0.25, 0.10], $returnedCoins);
        $this->assertEmpty($moneyManager->getInsertedCoins());
        $this->assertEquals(0, $moneyManager->getTotal());
    }

    public function testReset()
    {
        $moneyManager = new UserMoneyManager();
        $moneyManager->insertCoin(0.50);
        $moneyManager->insertCoin(0.25);
        $moneyManager->reset();
        $this->assertEmpty($moneyManager->getInsertedCoins());
        $this->assertEquals(0, $moneyManager->getTotal());
    }

    public function testGetInsertedCoins()
    {
        $moneyManager = new UserMoneyManager();
        $this->assertEmpty($moneyManager->getInsertedCoins());
        $moneyManager->insertCoin(0.10);
        $moneyManager->insertCoin(0.05);
        $this->assertEquals([0.10, 0.05], $moneyManager->getInsertedCoins());
    }
}
