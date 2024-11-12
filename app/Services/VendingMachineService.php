<?php

namespace App\Services;

use App\DataProviders\VendingMachine\DataProvider;
use App\Models\VendingMachine;
use App\Presenters\VendingMachineResultPresenter;

class VendingMachineService
{
    private VendingMachine $vendingMachine;
    private DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->refreshVendingMachine();
    }

    public function refreshVendingMachine(): void
    {
        $this->vendingMachine = $this->dataProvider->getVendingMachine();
    }

    public function getViewData($result = null): array
    {
        $this->refreshVendingMachine();

        $presentedResult = $result ? new VendingMachineResultPresenter($result) : null;

        //@todo depend on an interface, elsewhere too
        return [
            'inventory' => $this->vendingMachine->getInventory(),
            'displayMessage' => $this->vendingMachine->getDisplayMessage(),
            'insertedCoins' => $this->vendingMachine->getInsertedCoins(),
            'totalInserted' => $this->vendingMachine->getInsertedCoinsTotal(),
            'presentedResult' => $presentedResult,
        ];
    }

    public function insertCoin(int $coin): void
    {
        $this->refreshVendingMachine();
        $this->vendingMachine->insertCoin($coin);
        $this->dataProvider->saveVendingMachine($this->vendingMachine);
    }

    public function selectItem(string $itemCode): array
    {
        $this->refreshVendingMachine();
        $result = $this->vendingMachine->selectItem($itemCode);
        $this->dataProvider->saveVendingMachine($this->vendingMachine);
        return $result;
    }

    public function service(array $items, array $coins): void
    {
        $this->refreshVendingMachine();
        $this->vendingMachine->service($items, $coins);
        $this->dataProvider->saveVendingMachine($this->vendingMachine);
    }
}
