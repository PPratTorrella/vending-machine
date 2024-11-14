<?php

namespace App\Commands\Concrete\VendingMachine;

use App\Commands\Interfaces\Command;
use App\Engine\Interfaces\VendingMachineInterface;

class PunchCommand implements Command
{
    private VendingMachineInterface $machine;

    public function __construct(VendingMachineInterface $machine)
    {
        $this->machine = $machine;
    }

    public function execute(): void
    {
        // we could delete some coins or items for example
    }
}
