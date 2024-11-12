<?php
namespace App\Repositories\VendingMachine;

use App\Factories\VendingMachineStateFactory;
use App\Models\VendingMachine;
use App\Repositories\VendingMachine\Interfaces\StorageInterface;
use Exception;
use Illuminate\Contracts\Session\Session;

class SessionStorage implements StorageInterface
{
    protected Session $session;
    protected VendingMachineStateFactory $stateFactory;

    public function __construct(Session $session, VendingMachineStateFactory $stateFactory)
    {
        $this->session = $session;
        $this->stateFactory = $stateFactory;
    }

    public function getVendingMachine(): VendingMachine
    {
        if (!$this->session->has('vendingMachine')) {
            return $this->initDefault();
        }

        $vendingMachineData = $this->session->get('vendingMachine');
        $vendingMachine = new VendingMachine();

        $items = array_map(function ($itemData) {
            return [
                'name' => $itemData['item']->getName(),
                'count' => $itemData['count'],
                'price' => $itemData['item']->getPrice(),
            ];
        }, $vendingMachineData['inventory']['items']);
        $coins = $vendingMachineData['inventory']['coins'];
        $vendingMachine->updateInventory($items, $coins);
        $vendingMachine->setInsertedCoins($vendingMachineData['insertedCoins']);

        try {
            $state = $this->stateFactory->create($vendingMachineData['state'], $vendingMachine);
            $vendingMachine->setState($state);
        } catch (Exception $e) {
            $vendingMachine->setIdleState();
        }

        $vendingMachine->setDisplayMessage($vendingMachineData['displayMessage']);

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

        $this->session->put('vendingMachine', $vendingMachineData);
    }

    public function initDefault(): VendingMachine
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