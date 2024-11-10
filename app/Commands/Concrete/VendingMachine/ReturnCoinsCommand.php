<?php

namespace App\Commands\Concrete\VendingMachine;

use App\Commands\Interfaces\Command;
use App\Models\VendingMachine;
use Exception;

class ReturnCoinsCommand implements Command
{
    private VendingMachine $machine;

    public function __construct(VendingMachine $machine)
    {
        $this->machine = $machine;
    }

    public function execute(): array
    {
        return $this->machine->userMoneyManager->returnCoins();
    }

}
