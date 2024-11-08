<?php

namespace App\Models;

use App\States\Interfaces\VendingMachineState;
use App\States\Concrete\IdleState;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendingMachine
{
    use HasFactory;

    public VendingMachineState $state;
    public $inventory; // @todo make some class

    // @todo main logic or service

    public function __construct()
    {
        $this->inventory = [];
        $this->state = new IdleState();
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    // @todo for all these entry points for the commands we delegate to state which is nice, but check if not too much duplication if too similar logic
    public function insertCoin($value)
    {
        $this->state->insertCoin($value);
    }

    public function returnCoins()
    {
        return $this->state->returnCoins();
    }

    public function selectItem($itemCode)
    {
        return $this->state->selectItem($itemCode);
    }

    public function service()
    {
        return $this->state->service('what needs to pass, some action commands?');
    }

}
