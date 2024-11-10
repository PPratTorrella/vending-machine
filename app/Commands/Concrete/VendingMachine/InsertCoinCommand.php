<?php

namespace App\Commands\Concrete\VendingMachine;

use App\Commands\Interfaces\Command;
use App\Models\VendingMachine;

class InsertCoinCommand implements Command
{
    private VendingMachine $machine;
    private int $coin;

    public function __construct(VendingMachine $machine, $coin)
    {
        $this->machine = $machine;
        $this->coin = $coin;
    }

    public function execute(): void
    {
        $this->machine->userMoneyManager->insertCoin($this->coin);
    }

}
