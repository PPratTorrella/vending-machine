<?php

namespace App\Dataproviders\VendingMachine;

use App\Factories\VendingMachineStateFactory;
use App\Models\VendingMachine;
use App\Commands\Concrete\VendingMachine\ServiceCommand;
use Exception;

class DataProvider
{
    public function getVendingMachine(): VendingMachine
    {
        if (!session()->has('vendingMachine')) {
            return $this->initializeDefaultInventory();
        }

        $vendingMachineData = session('vendingMachine');
        $vendingMachine = new VendingMachine();

        $items = array_map(function ($itemData) {
            return [
                'name' => $itemData['item']->getName(),
                'count' => $itemData['count'],
                'price' => $itemData['item']->getPrice(),
            ];
        }, $vendingMachineData['inventory']['items']);

        $coins = $vendingMachineData['inventory']['coins'];

        $command = new ServiceCommand($vendingMachine, $items, $coins);
        $command->execute();

        $vendingMachine->userMoneyManager->insertedCoins = $vendingMachineData['insertedCoins'];
        $vendingMachine->setDisplayMessage($vendingMachineData['displayMessage']);

        try {
            $state = VendingMachineStateFactory::create($vendingMachineData['state'], $vendingMachine);
            $vendingMachine->state = $state;
        } catch (Exception $e) {
            $vendingMachine->setIdleState();
        }

        return $vendingMachine;
    }

    public function saveVendingMachine(VendingMachine $vendingMachine): void
    {
        $vendingMachineData = [
            'inventory' => $vendingMachine->getInventory(),
            'insertedCoins' => $vendingMachine->userMoneyManager->getInsertedCoins(),
            'displayMessage' => $vendingMachine->displayMessage,
            'state' => $vendingMachine->state->getName(),
        ];

        session(['vendingMachine' => $vendingMachineData]);
    }

    private function initializeDefaultInventory(): VendingMachine
    {
        $vendingMachine = new VendingMachine();
        $vendingMachine->inventory->updateInventory([
            '55' => ['name' => 'Soda', 'price' => 150, 'count' => 10],
            '60' => ['name' => 'Water', 'price' => 65, 'count' => 10],
            '65' => ['name' => 'Juice', 'price' => 120, 'count' => 10],
        ], [
            100 => 10,
            50 => 20,
            25 => 30,
            10 => 40,
        ]);

        $this->saveVendingMachine($vendingMachine);

        return $vendingMachine;
    }
}
