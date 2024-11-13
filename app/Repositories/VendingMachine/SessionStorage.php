<?php
namespace App\Repositories\VendingMachine;

use App\Factories\VendingMachineStateFactory;
use App\Models\Interfaces\VendingMachineInterface;
use App\Repositories\VendingMachine\Interfaces\StorageInterface;
use Exception;
use Illuminate\Contracts\Session\Session;

class SessionStorage implements StorageInterface
{
    protected Session $session;
    protected VendingMachineStateFactory $stateFactory;
    private array $defaultItems;
    private array $defaultCoins;
    private $vendingMachineFactory; // callable for simple factory injection

    public function __construct(Session $session, VendingMachineStateFactory $stateFactory, array $defaultItems, array $defaultCoins, callable $vendingMachineFactory)
    {
        $this->session = $session;
        $this->stateFactory = $stateFactory;
        $this->defaultItems = $defaultItems;
        $this->defaultCoins = $defaultCoins;
        $this->vendingMachineFactory = $vendingMachineFactory;
    }

    protected function createVendingMachine(): VendingMachineInterface
    {
        return call_user_func($this->vendingMachineFactory);
    }

    public function getVendingMachine(): VendingMachineInterface
    {
        if (!$this->session->has('vendingMachine')) {
            return $this->initDefault();
        }

        $vendingMachineData = $this->session->get('vendingMachine');
        $vendingMachine = $this->createVendingMachine();

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
        } catch (Exception) {
            $vendingMachine->setIdleState();
        }

        $vendingMachine->setDisplayMessage($vendingMachineData['displayMessage']);

        return $vendingMachine;
    }

    public function saveVendingMachine(VendingMachineInterface $vendingMachine): void
    {
        $vendingMachineData = [
            'inventory' => $vendingMachine->getInventory(),
            'insertedCoins' => $vendingMachine->userMoneyManager->getInsertedCoins(),
            'displayMessage' => $vendingMachine->getDisplayMessage(),
            'state' => $vendingMachine->state->getName(),
        ];

        $this->session->put('vendingMachine', $vendingMachineData);
    }

    public function initDefault(): VendingMachineInterface
    {
        $vendingMachine = $this->createVendingMachine();
        $vendingMachine->inventory->updateInventory($this->defaultItems, $this->defaultCoins);
        $this->saveVendingMachine($vendingMachine);
        return $vendingMachine;
    }
}
