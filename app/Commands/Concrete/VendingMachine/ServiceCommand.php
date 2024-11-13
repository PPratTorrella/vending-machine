<?php

namespace App\Commands\Concrete\VendingMachine;

use App\Commands\Interfaces\Command;
use App\Models\Interfaces\VendingMachineInterface;

class ServiceCommand implements Command
{
    private VendingMachineInterface $machine;
    private array $items;
    private array $coins;

    public function __construct(VendingMachineInterface $machine, $items = [], $coins = [])
    {
        $this->machine = $machine;
        $this->items = $items;
        $this->coins = $coins;
    }

    public function execute(): void
    {
        $this->machine->inventory->updateInventory($this->items, $this->coins);
    }
}
