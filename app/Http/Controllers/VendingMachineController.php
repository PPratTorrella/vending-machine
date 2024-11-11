<?php

namespace App\Http\Controllers;

use App\Services\VendingMachineService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VendingMachineController extends Controller
{
    private VendingMachineService $vendingMachineService;

    public function __construct(VendingMachineService $vendingMachineService)
    {
        $this->vendingMachineService = $vendingMachineService;
    }

    public function show()
    {
        $viewData = $this->vendingMachineService->getViewData();
        return view('vendingMachine.index', $viewData);
    }

    public function insertCoin(Request $request)
    {
        $coin = (int)$request->input('coin');
        try {
            $this->vendingMachineService->insertCoin($coin);
        } catch (Exception $e) {
            session()->flash('message', $e->getMessage());
        }
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
        session()->flash('result', $result ?? []);
        return redirect()->route('vendingMachine.show');
    }

    public function service(Request $request)
    {
        $items = json_decode($request->input('items'), true) ?? [];
        $coins = json_decode($request->input('coins'), true) ?? [];
        try {
            $this->vendingMachineService->service($items, $coins);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
        session()->flash('message', $errorMessage ?? 'Service completed.');
        return redirect()->route('vendingMachine.show');
    }
}
