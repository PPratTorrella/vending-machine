<?php

namespace App\Commands\Concrete\VendingMachine;

use App\Commands\Interfaces\Command;
use App\Models\VendingMachine;
use Illuminate\Support\Facades\Config;

class InsertCoinCommand implements Command
{
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
            return [$this->coin];
        }
        $this->machine->userMoneyManager->insertCoin($this->coin);
        return []; // sucess
    }

    private function isValidDenomination(int $coin): bool
    {
        $validCoins = Config::get('vending.valid_coins', []);
        return in_array($coin, $validCoins, true);
    }
}
