<?php

namespace App\Services;

use App\DataProviders\VendingMachine\DataProvider;
use App\Engine\Interfaces\VendingMachineInterface;

class VendingMachineService
{
    private VendingMachineInterface $vendingMachine;
    private DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function refreshVendingMachine(): void
    {
        $this->vendingMachine = $this->dataProvider->getVendingMachine();
    }

    public function initDefault(): VendingMachineInterface
    {
        return $this->dataProvider->initDefault();
    }

    public function getViewData(): array
    {
        $this->refreshVendingMachine();

        return [
            'inventory' => $this->vendingMachine->getInventory(),
            'displayMessage' => $this->vendingMachine->getDisplayMessage(),
            'insertedCoins' => $this->vendingMachine->getInsertedCoins(),
            'totalInserted' => $this->vendingMachine->getInsertedCoinsTotal(),
            'stateName' => $this->vendingMachine->getStateName(),
        ];
    }

    public function insertCoin(int $coin): array
    {
        $this->refreshVendingMachine();
        $result = $this->vendingMachine->insertCoin($coin);
        $this->dataProvider->saveVendingMachine($this->vendingMachine);
        return $result;
    }

    public function selectItem(string $itemCode): array
    {
        $this->refreshVendingMachine();
        $result = $this->vendingMachine->selectItem($itemCode);
        $this->dataProvider->saveVendingMachine($this->vendingMachine);
        return $result;
    }

    public function service(array $items, array $coins): bool
    {
        $this->refreshVendingMachine();
        $result = $this->vendingMachine->service($items, $coins);
        $this->dataProvider->saveVendingMachine($this->vendingMachine);
        return $result;
    }

    public function returnCoins(): array
    {
        $this->refreshVendingMachine();
        $result = $this->vendingMachine->returnCoins();
        $this->dataProvider->saveVendingMachine($this->vendingMachine);
        return $result;
    }

    public function punch(): void
    {
        $this->refreshVendingMachine();
        $this->vendingMachine->punch();
        $this->dataProvider->saveVendingMachine($this->vendingMachine);
    }
}
