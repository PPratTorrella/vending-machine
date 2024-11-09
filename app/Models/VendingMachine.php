<?php

namespace App\Models;

use App\Services\Inventory;
use App\Services\UserMoneyManager;
use App\States\Concrete\HasMoneyState;
use App\States\Concrete\IdleState;
use App\States\Interfaces\VendingMachineState;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendingMachine
{
    use HasFactory;

    public HasMoneyState $hasMoneyState;
    public IdleState $idleState;
    public VendingMachineState $state;
    public Inventory $inventory;
    public UserMoneyManager $userMoneyManager;
    public string $displayMessage;


    public function __construct()
    {
        $this->inventory = app(Inventory::class);
        $this->idleState = new IdleState($this);
        $this->hasMoneyState = new HasMoneyState($this);
        $this->state = $this->idleState;
        $this->displayMessage = IdleState::DISPLAY_MESSAGE;
        $this->userMoneyManager = new UserMoneyManager();
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function insertCoin($value)
    {
        $this->state->insertCoin($value);
    }

    public function returnCoins()
    {
        $coins = $this->state->returnCoins();
        $this->state = $this->idleState;
        return $coins;
    }

    public function selectItem($itemCode)
    {
        $return = [
            'item'  => null,
            'change' => [],
        ];

        try {
            return $this->state->selectItem($itemCode);
        } catch (Exception $e) {
            $this->displayMessage = $e->getMessage();
            return $return;
        }
    }

    public function service($action, $parameters = [])
    {
        return $this->state->service('xxxxx');
    }

    public function getInsertedCoins()
    {
        return $this->userMoneyManager->getInsertedCoins();
    }

    public function reset()
    {
        $this->userMoneyManager->reset();
    }

}
