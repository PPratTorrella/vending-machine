<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ChangeCalculatorHelper;
use PHPUnit\Framework\TestCase;

class ChangeCalculatorHelperTest extends TestCase
{
    public function testCalculateOptimalChangeWithExactAmount()
    {
        $availableCoins = [100 => 5, 25 => 10, 10 => 10, 5 => 10];

        // Test exact amounts
        $helper = new ChangeCalculatorHelper();
        $change = $helper->calculateOptimalChange(100, $availableCoins);
        $this->assertEquals([100], $change, 'Should return single 1.00 coin for 1.00');

        $change = $helper->calculateOptimalChange(25, $availableCoins);
        $this->assertEquals([25], $change, 'Should return single 0.25 coin for 0.25');
    }

    public function testCalculateOptimalChangeWithMultipleCoins()
    {
        $availableCoins = [100 => 5, 25 => 10, 10 => 10, 5 => 10];
        $helper = new ChangeCalculatorHelper();

        // Test 1.40 = 1.00 + 0.25 + 0.10 + 0.05
        $change = $helper->calculateOptimalChange(140, $availableCoins);
        $this->assertEquals([100, 25, 10, 5], $change, 'Should return optimal combination for 1.40');

        // Test 0.95 = 0.25 + 0.25 + 0.25 + 0.10 + 0.10
        $change = $helper->calculateOptimalChange(95, $availableCoins);
        $this->assertEquals([25, 25, 25, 10, 10], $change, 'Should return optimal combination for 0.95');
    }

    public function testCalculateOptimalChangeWithImpossibleAmount()
    {
        $availableCoins = [100 => 5, 25 => 10, 10 => 10, 5 => 10];
        $helper = new ChangeCalculatorHelper();

        // Test amount smaller than smallest coin
        $change = $helper->calculateOptimalChange(3, $availableCoins);
        $this->assertNull($change, 'Should return null for amount smaller than smallest coin');

        // Test amount that cannot be made with available coins
        $change = $helper->calculateOptimalChange(7, $availableCoins);
        $this->assertNull($change, 'Should return null for amount that cannot be made with available coins');
    }

    public function testCalculateOptimalChangeWithLimitedInventory()
    {
        $limitedCoins = [100 => 0, 25 => 1, 10 => 1, 5 => 1];
        $helper = new ChangeCalculatorHelper();

        // Test when we need more coins than available
        $change = $helper->calculateOptimalChange(50, $limitedCoins);
        $this->assertNull($change, 'Should return null when not enough coins available');

        // Test when exact change can be made with limited inventory
        $change = $helper->calculateOptimalChange(40, $limitedCoins);
        $this->assertEquals([25, 10, 5], $change, 'Should return correct change with limited inventory');
    }

    public function testCalculateOptimalChangeEdgeCases()
    {
        $availableCoins = [100 => 5, 25 => 10, 10 => 10, 5 => 10];
        $helper = new ChangeCalculatorHelper();

        // Test zero amount
        $change = $helper->calculateOptimalChange(0, $availableCoins);
        $this->assertEquals([], $change, 'Should return empty array for zero amount');

        // Test maximum reasonable amount
        $change = $helper->calculateOptimalChange(500, $availableCoins);
        $this->assertCount(5, $change, 'Should return 5 x 1.00 coins for 5.00');
        $this->assertEquals(array_fill(0, 5, 100), $change);
    }

    public function testCalculateOptimalChangeFloatingPointPrecision()
    {
        $availableCoins = [100 => 5, 25 => 10, 10 => 10, 5 => 10];
        $helper = new ChangeCalculatorHelper();

        // Test amount that could cause floating point precision issues
        $change = $helper->calculateOptimalChange(35, $availableCoins);
        $this->assertEquals([25, 10], $change, 'Should handle floating point arithmetic correctly');

        $change = $helper->calculateOptimalChange(130, $availableCoins);
        $this->assertEquals([100, 25, 5], $change, 'Should handle floating point arithmetic correctly');
    }

    public function testCalculateOptimalChangeWithGreedyFallback()
    {
        $availableCoins = [25 => 10, 10 => 10];
        $helper = new ChangeCalculatorHelper();

        $change = $helper->calculateOptimalChange(40, $availableCoins);
        $this->assertEquals([10, 10, 10, 10], $change, 'Should find alternative solution when optimal coins unavailable');
    }
}
