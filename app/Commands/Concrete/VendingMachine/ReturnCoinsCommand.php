<?php

namespace App\Commands\Concrete\VendingMachine;

use App\Commands\Interfaces\Command;
use App\Models\Interfaces\VendingMachineInterface;

class ReturnCoinsCommand implements Command
{
    private VendingMachineInterface $machine;

    public function __construct(VendingMachineInterface $machine)
    {
        $this->machine = $machine;
    }

    public function execute(): array
    {
        return $this->machine->userMoneyManager->returnCoins();
    }

}
