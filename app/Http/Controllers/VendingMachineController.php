<?php

namespace App\Http\Controllers;

use App\Presenters\VendingMachinePresenter;
use App\Services\VendingMachineService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class VendingMachineController extends Controller
{
    private VendingMachineService $vendingMachineService;

    public function __construct(VendingMachineService $vendingMachineService)
    {
        $this->vendingMachineService = $vendingMachineService;
    }

    public function show(VendingMachinePresenter $presenter)
    {
        $dispensed = session('dispensed', []);
        if (!empty($dispensed['item'])) {
            $presenter->setItem($dispensed['item']);
        }
        if (!empty($dispensed['coins'])) {
            $presenter->setCoins($dispensed['coins']);
        }

        $validCoins = Config::get('vending.valid_coins', []);
        $coinLabels = collect($validCoins)->mapWithKeys(fn($coin) => [$coin => $presenter->formatPrice($coin)]);

        $viewData = array_merge(
            $this->vendingMachineService->getViewData(),
            compact('presenter', 'coinLabels')
        );

        return view('vendingMachine.index', $viewData);
    }

    public function insertCoin(Request $request)
    {
        $coin = (int)$request->input('coin');
        try {
            $coins = $this->vendingMachineService->insertCoin($coin);
            if (!empty($coins)) {
                session()->flash('dispensed', ['coins' => $coins]);
            }
        } catch (Exception $e) {
            session()->flash('message', $e->getMessage());
        }
        return redirect()->route('vendingMachine.show');
    }

    public function returnCoins()
    {
        try {
            $coins = $this->vendingMachineService->returnCoins();
        } catch (Exception $e) {
            session()->flash('message', $e->getMessage());
        }
        session()->flash('dispensed', ['coins' => $coins ?? []]);
        return redirect()->route('vendingMachine.show');
    }

    public function selectItem(Request $request)
    {
        $itemCode = $request->input('item_code');
        try {
            $result = $this->vendingMachineService->selectItem($itemCode);
        } catch (Exception $e) {
            session()->flash('message', $e->getMessage());
        }
        session()->flash('dispensed', $result ?? []);
        return redirect()->route('vendingMachine.show');
    }

    public function service(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|json',
            'coins' => 'required|json',
        ]);

        if ($validator->fails()) {
            session()->flash('message', 'Invalid JSON format for items or coins.');
            return redirect()->route('vendingMachine.show');
        }

        $items = json_decode($request->input('items'), true);
        $coins = json_decode($request->input('coins'), true);

        $structureValidator = Validator::make(
            ['items' => $items, 'coins' => $coins],
            [
                'items' => 'required|array',
                'items.*.name' => 'required|string',
                'items.*.price' => 'required|integer|min:0',
                'items.*.count' => 'required|integer|min:0',
                'coins' => 'required|array',
                'coins.*' => 'integer|min:0',
            ]
        );

        if ($structureValidator->fails()) {
            session()->flash('message', 'Invalid structure in items or coins.');
            return redirect()->route('vendingMachine.show');
        }

        try {
            $this->vendingMachineService->service($items, $coins);
        } catch (Exception $e) {
            session()->flash('message', $e->getMessage());
        }
        return redirect()->route('vendingMachine.show');
    }
}
