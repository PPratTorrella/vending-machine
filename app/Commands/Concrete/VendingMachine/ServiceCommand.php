<?php

namespace App\Commands\Concrete\VendingMachine;

use App\Commands\Interfaces\Command;
use App\Models\VendingMachine;

class ServiceCommand implements Command
{
    private VendingMachine $machine;
    private mixed $items;
    private mixed $coins;

    public function __construct(VendingMachine $machine, $items = [], $coins = [])
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
