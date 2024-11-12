<?php

namespace App\Commands\Concrete\VendingMachine;

use App\Commands\Interfaces\Command;
use App\Models\VendingMachine;

class InsertCoinCommand implements Command
{
    private const VALID_COINS = [100, 25, 10, 5];
    private VendingMachine $machine;
    private int $coin;

    public function __construct(VendingMachine $machine, $coin)
    {
        $this->machine = $machine;
        $this->coin = $coin;
    }

    public function execute(): array
    {
        if (!$this->isValidDenomination($this->coin)) {
            return ['coin' => $this->coin];  // Return invalid coin for refund
        }
        $this->machine->userMoneyManager->insertCoin($this->coin);
        return []; // sucess
    }

    private function isValidDenomination(int $coin): bool {
        return in_array($coin, self::VALID_COINS, true);
    }
}
