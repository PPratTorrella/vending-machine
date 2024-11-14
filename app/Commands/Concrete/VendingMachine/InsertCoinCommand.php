<?php

namespace App\Commands\Concrete\VendingMachine;

use App\Commands\Interfaces\Command;
use App\Engine\Interfaces\VendingMachineInterface;
use Illuminate\Support\Facades\Config;

class InsertCoinCommand implements Command
{
    private VendingMachineInterface $machine;
    private int $coin;

    public function __construct(VendingMachineInterface $machine, $coin)
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
