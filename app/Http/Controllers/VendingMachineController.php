<?php

namespace App\Http\Controllers;

use App\Models\VendingMachine;
use App\States\Concrete\HasMoneyState;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VendingMachineController extends Controller
{
    private VendingMachine $vendingMachine;

    public function __construct()
    {
        if (session()->has('vendingMachine')) {
            $vendingMachineData = session('vendingMachine');
            $this->vendingMachine = new VendingMachine();

            $this->vendingMachine->inventory->updateInventory(
                $vendingMachineData['inventory']['items'],
                $vendingMachineData['inventory']['coins']
            );

            $this->vendingMachine->userMoneyManager->insertedCoins = $vendingMachineData['insertedCoins'];

            $this->vendingMachine->displayMessage = $vendingMachineData['displayMessage'];

            if ($vendingMachineData['state'] === 'hasMoney') {
                $this->vendingMachine->setHasMoneyState();
            } else {
                $this->vendingMachine->setIdleState();
            }
        } else {
            $this->vendingMachine = new VendingMachine();
            $this->vendingMachine->inventory->updateInventory([
                '55' => ['name' => 'Soda', 'price' => 150, 'count' => 10],
                '60' => ['name' => 'Water', 'price' => 100, 'count' => 10],
                '65' => ['name' => 'Juice', 'price' => 120, 'count' => 10],
            ], [
                100 => 10,
                50 => 20,
                25 => 30,
                10 => 40,
            ]);
        }
    }

    public function show()
    {
        return view('vendingMachine.index', [
            'inventory' => $this->vendingMachine->getInventory(),
            'displayMessage' => $this->vendingMachine->displayMessage,
            'insertedCoins' => $this->vendingMachine->userMoneyManager->getInsertedCoins(),
            'totalInserted' => $this->vendingMachine->userMoneyManager->getTotal(),
        ]);
    }

    public function insertCoin(Request $request)
    {
        $coin = (int)$request->input('coin');
        $this->vendingMachine->insertCoin($coin);

        $this->storeSessionData();

        return redirect()->route('vendingMachine.show');
    }

    public function selectItem(Request $request)
    {
        $itemCode = $request->input('item_code');
        $this->vendingMachine->selectItem($itemCode);

        $this->storeSessionData();

        return redirect()->route('vendingMachine.show');
    }

    public function service(Request $request)
    {
        $itemsJson = $request->input('items');
        $coinsJson = $request->input('coins');

        $items = json_decode($itemsJson, true);
        $coins = json_decode($coinsJson, true);

        $this->vendingMachine->service($items ?? [], $coins ?? []);

        $this->storeSessionData();

        return redirect()->route('vendingMachine.show');
    }

    private function storeSessionData()
    {
        $vendingMachineData = [
            'inventory' => $this->vendingMachine->getInventory(),
            'insertedCoins' => $this->vendingMachine->userMoneyManager->insertedCoins,
            'displayMessage' => $this->vendingMachine->displayMessage,
            'state' => $this->vendingMachine->state instanceof HasMoneyState ? 'hasMoney' : 'idle',
        ];

        session(['vendingMachine' => $vendingMachineData]);
    }
}
