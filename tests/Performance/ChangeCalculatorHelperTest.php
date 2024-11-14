<?php

namespace Tests\Performance;

use App\Helpers\ChangeCalculatorHelper;
use PHPUnit\Framework\TestCase;

class ChangeCalculatorHelperTest extends TestCase
{
    public function testCalculateOptimalChangePerformanceWithMemoryDifference()
    {
        ini_set('memory_limit', '512M');

        $availableCoins = [100 => 999999, 25 => 999999, 10 => 999999, 5 => 999999, 1 => 999999];
        $helper = new ChangeCalculatorHelper();
        $amounts = [10000, 100000, 1000000, 10000000, 132254200];

        $initialMemoryMB = number_format(memory_get_usage(true) / (1024 * 1024), 2);

        $start = microtime(true);

        foreach ($amounts as $amount) {
            $change = $helper->calculateOptimalChange($amount, $availableCoins);
            $this->assertNotNull($change, "Failed to calculate change for $amount");
        }

        $end = microtime(true);
        $duration = number_format($end - $start, 5);

        $peakMemoryMB = number_format(memory_get_peak_usage(true) / (1024 * 1024), 2);
        $memoryDifference = number_format($peakMemoryMB - $initialMemoryMB, 2);

        echo "testCalculateOptimalChangePerformanceWithMemoryDifference\n";
        echo "Performance test completed in $duration seconds\n";
        echo "Initial memory usage: $initialMemoryMB MB\n";
        echo "Peak memory usage: $peakMemoryMB MB\n";
        echo "Memory increase during execution: $memoryDifference MB\n";

        $this->assertLessThan(10, $duration, 'Performance threshold exceeded');
        $this->assertLessThan(120, $peakMemoryMB, 'Memory usage threshold exceeded');
        $this->assertLessThan(110, $memoryDifference, 'Excessive memory usage increase');
    }
}
