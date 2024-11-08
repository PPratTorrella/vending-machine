<?php

namespace App\Models;

use App\Services\MoneyManager;
use App\States\Concrete\HasMoneyState;
use App\States\Concrete\IdleState;
use App\States\Interfaces\VendingMachineState;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendingMachine
{
    use HasFactory;

    public HasMoneyState $hasMoneyState;
    public IdleState $idleState;
    public VendingMachineState $state;
    public $inventory; // @todo make some class
    public MoneyManager $moneyManager;


    public function __construct()
    {
        $this->inventory = [];
        $this->idleState = new IdleState($this);
        $this->hasMoneyState = new HasMoneyState($this);
        $this->state = $this->idleState;
        $this->moneyManager = new MoneyManager();
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    // @todo much logic duplication? But at least wont grow in conditions infinitly so maybe ok
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

    public function getInsertedCoins()
    {
        return $this->moneyManager->getInsertedCoins();
    }

}
