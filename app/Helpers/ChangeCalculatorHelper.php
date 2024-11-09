<?php

namespace App\Helpers;

use InvalidArgumentException;

class ChangeCalculatorHelper
{
    /**
     * Calculate the optimal change combination for a given amount
     *
     * @param float $amount Amount to make change for in full euros
     * @param array $availableCoins Associative array of coin values in cents and their counts
     * @return array|null Array of coin values to return as change, or null if exact change cannot be made
     * @throws InvalidArgumentException If input parameters are invalid
     */
    public static function calculateOptimalChange(float $amount, array $availableCoins): ?array
    {
        $amount = $amount * 100; // convert to cents to match coin values;

        self::validate($amount, $availableCoins);

        $workingCoins = $availableCoins;
        $change = [];
        $remaining = round($amount, 2);

        if ($remaining === 0.0) {
            return [];
        }

        $coinValues = array_keys($workingCoins);
        // in descending order to give back least amount of coins
        sort($coinValues, SORT_NUMERIC);
        $coinValues = array_reverse($coinValues);

        foreach ($coinValues as $coinValue) {
            while (true) {
                $isLeftAmount = $remaining >= $coinValue;
                $hasCoins = $workingCoins[$coinValue] > 0;

                if (!$isLeftAmount || !$hasCoins) {
                    break;
                }

                $possibleAmountUsed = min(
                    (int)floor($remaining / $coinValue),
                    $workingCoins[$coinValue]
                );

                if ($possibleAmountUsed <= 0) {
                    break;
                }

                $testRemainingAmount = round($remaining - ($coinValue * $possibleAmountUsed), 2);

                // check (recursively) if remainder ends up being solvable or not
                if (self::canMakeRemainingChange($testRemainingAmount, $workingCoins, $coinValues, $coinValue)) {

                    // add coins to our change and update remaining amount
                    for ($i = 0; $i < $possibleAmountUsed; $i++) {
                        $change[] = $coinValue;
                        $workingCoins[$coinValue]--;
                    }
                    $remaining = $testRemainingAmount;

                    if ($remaining === 0.0) {
                        return $change;
                    }
                } else {
                    // continue to smaller coins
                    break;
                }
            }
        }

        // could not make exact change
        return null;
    }

    /**
     * Check if we can make exact change for the remaining amount using smaller coins
     *
     * @param float $amount Remaining amount to make change for
     * @param array $availableCoins Available coins and their counts
     * @param array $denominations Sorted array of coin denominations
     * @param float $currentCoin Current coin denomination being considered
     * @return bool Whether exact change can be made
     */
    private static function canMakeRemainingChange(
        float $amount,
        array $availableCoins,
        array $denominations,
        float $currentCoin
    ): bool {
        // base cases
        if ($amount === 0.0) {
            return true;
        }
        if ($amount < 0.0) {
            return false;
        }

        // Get index of current coin to only look at smaller denominations
        $currentIndex = array_search($currentCoin, $denominations);
        if ($currentIndex === false) {
            return false;
        }
        $startIndex = $currentIndex + 1;

        // Try each smaller denomination
        for ($i = $startIndex; $i < count($denominations); $i++) {
            $coinValue = $denominations[$i];

            if ($coinValue <= 0) {
                continue; // Skip invalid coin values
            }

            if ($availableCoins[$coinValue] > 0 && $amount >= $coinValue) {
                // Calculate maximum number of this coin we could use
                $maxCoins = min(
                    (int)floor($amount / $coinValue),
                    $availableCoins[$coinValue]
                );

                // Try different numbers of this coin
                for ($numCoins = $maxCoins; $numCoins > 0; $numCoins--) {
                    $tempCoins = $availableCoins;
                    $tempCoins[$coinValue] -= $numCoins;
                    $newAmount = round($amount - ($coinValue * $numCoins), 2);

                    if (self::canMakeRemainingChange($newAmount, $tempCoins, $denominations, $coinValue)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param float $amount
     * @param array $availableCoins
     * @return void
     */
    public static function validate(float $amount, array $availableCoins): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException("Amount cannot be negative");
        }

        if (empty($availableCoins)) {
            throw new InvalidArgumentException("Available coins array cannot be empty");
        }

        foreach ($availableCoins as $value => $count) {
            if ($value <= 0) {
                throw new InvalidArgumentException("Coin values must be positive");
            }
            if (!is_numeric($count) || $count < 0) {
                throw new InvalidArgumentException("Coin counts must be non-negative numbers");
            }
        }
    }
}
