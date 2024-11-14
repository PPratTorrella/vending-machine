<?php

namespace App\States\Concrete;

use App\Commands\Concrete\VendingMachine\InsertCoinCommand;
use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Commands\Concrete\VendingMachine\ServiceCommand;
use App\Factories\VendingMachineStateFactory;
use App\Engine\VendingMachine;
use App\States\Interfaces\VendingMachineStateInterface;

class BrokenState implements VendingMachineStateInterface
{
    const DISPLAY_MESSAGE = 'Broken machine. Do not insert coins!! Need service.';
    const SELECTED_ITEM_MESSAGE = 'You can\'t buy anything machine is very broken...';
    const RETURN_COINS_MESSAGE = 'Hmmm no, cant do that, way too broken.';
    const INSERTED_COIN_NOT_VALID = 'invalid coin.';
    const SERVICE_MESSAGE = 'Fresh stock and fixed machine! :).';
    const INSERTED_COIN = 'Coin inserted? You trust that this broken machine wont keep it?';
    const STATE_NAME = VendingMachineStateFactory::BROKEN_STATE_NAME;
    const PUNCH_MESSAGE = 'Who punches a broken machine?';

    private VendingMachine $machine;

    public function __construct($machine)
    {
        $this->machine = $machine;
        $this->setMessage(null, true);
    }

    public function insertCoin($coin): array
    {
        $command = new InsertCoinCommand($this->machine, $coin);
        $result = $command->execute();
        if (empty($result)) {
            $this->setMessage(self::INSERTED_COIN);
        } else {
            $this->setMessage(self::INSERTED_COIN_NOT_VALID);
        }
        return $result;
    }

    public function returnCoins(): array
    {
        $this->setMessage(self::RETURN_COINS_MESSAGE);
        return [];
    }

    public function selectItem($itemCode): array
    {
        $this->setMessage(self::SELECTED_ITEM_MESSAGE);

        $command = new SelectItemCommand($this->machine, $itemCode, allowed: false);
        return $command->execute();
    }

    public function service($items = [], $coins = []): bool
    {
        $command = new ServiceCommand($this->machine, $items, $coins);
        $command->execute();
        $this->machine->setIdleState();
        $this->setMessage(self::SERVICE_MESSAGE);
        return true;
    }

    public function punch(): void
    {
        $this->setMessage(self::PUNCH_MESSAGE, true);
    }

    public function getName(): string
    {
        return self::STATE_NAME;
    }

    public function setMessage($message = null, $prefixDefault = false): void
    {
        if ($prefixDefault) {
            $message = self::DISPLAY_MESSAGE . ' '. $message;
        }
        $this->machine->setDisplayMessage($message);
    }
}
