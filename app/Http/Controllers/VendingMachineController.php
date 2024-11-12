<?php

namespace App\Http\Controllers;

use App\Presenters\VendingMachineResultPresenter;
use App\Services\VendingMachineService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class VendingMachineController extends Controller
{
    private VendingMachineService $vendingMachineService;

    public function __construct(VendingMachineService $vendingMachineService)
    {
        $this->vendingMachineService = $vendingMachineService;
    }

    public function show()
    {
        $result = session('result', []);
        $viewData = $this->vendingMachineService->getViewData($result);
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
            $errorMessage = $e->getMessage();
        }
        session()->flash('message', $errorMessage ?? 'Service completed.');
        return redirect()->route('vendingMachine.show');
    }
}
