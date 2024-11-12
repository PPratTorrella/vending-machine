<?php

namespace Tests\Unit\Commands\Concrete\VendingMachine;

use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Services\VendingMachineService;
use App\Models\VendingMachine;
use App\States\Concrete\HasMoneyState;
use Tests\TestCase;

class SelectItemCommandTest extends TestCase
{
    public function testExecuteWithSufficientFundsAndStock()
    {
        /** @var VendingMachineService $vendingMachineService */
        $vendingMachineService = app(VendingMachineService::class);

        $vendingMachine = $vendingMachineService->initDefault();

        $itemCode = '55'; // 150 cents default item (Soda)
        $vendingMachine->userMoneyManager->insertedCoins = [100, 100, 50]; // Total inserted: 250 cents

        $command = new SelectItemCommand($vendingMachine, $itemCode);
        $result = $command->execute();

        $this->assertNotNull($result['item']);
        $this->assertSame(150.0, $result['item']->getPrice());
        $this->assertSame('Soda', $result['item']->getName());
        $this->assertSame([100], $result['change']); // Change expected: 100 cents
    }

    public function testExecuteWithExactChange()
    {
        /** @var VendingMachineService $vendingMachineService */
        $vendingMachineService = app(VendingMachineService::class);

        $vendingMachine = $vendingMachineService->initDefault();

        $itemCode = '60'; // 65 cents default item (Water)
        $vendingMachine->userMoneyManager->insertedCoins = [50, 10, 5]; // Total inserted: 65 cents

        $command = new SelectItemCommand($vendingMachine, $itemCode);
        $result = $command->execute();

        $this->assertNotNull($result['item']);
        $this->assertSame(65.0, $result['item']->getPrice());
        $this->assertSame('Water', $result['item']->getName());
        $this->assertEmpty($result['change']); // No change expected
    }

    public function testExecuteWithInsufficientFunds()
    {
        /** @var VendingMachineService $vendingMachineService */
        $vendingMachineService = app(VendingMachineService::class);

        $vendingMachine = $vendingMachineService->initDefault();

        $itemCode = '55'; // 150 cents default item (Soda)
        $vendingMachine->userMoneyManager->insertedCoins = [50, 50]; // Total inserted: 100 cents

        $command = new SelectItemCommand($vendingMachine, $itemCode);
        $result = $command->execute();

        $this->assertNull($result['item']);
        $this->assertEmpty($result['change']);
        $this->assertStringContainsString(VendingMachine::ERROR_MESSAGE_INSUFFICIENT_FUNDS, $vendingMachine->displayMessage);
    }

    public function testExecuteWithOutOfStock()
    {
        /** @var VendingMachineService $vendingMachineService */
        $vendingMachineService = app(VendingMachineService::class);

        $vendingMachine = $vendingMachineService->initDefault();

        $itemCode = '65'; // 120 cents default item (Juice)
        $vendingMachine->inventory->items[$itemCode]['count'] = 0;

        $vendingMachine->userMoneyManager->insertedCoins = [100, 50]; // Total inserted: 150 cents

        $command = new SelectItemCommand($vendingMachine, $itemCode);
        $result = $command->execute();

        $this->assertNull($result['item']);
        $this->assertEmpty($result['change']);
        $this->assertStringContainsString(VendingMachine::ERROR_MESSAGE_OUT_OF_STOCK, $vendingMachine->displayMessage);
    }

    public function testExecuteWithInsufficientChange()
    {
        /** @var VendingMachineService $vendingMachineService */
        $vendingMachineService = app(VendingMachineService::class);

        $vendingMachine = $vendingMachineService->initDefault();

        $vendingMachine->inventory->coins = [];

        $itemCode = '60'; // 65 cents default item (Water)
        $vendingMachine->userMoneyManager->insertedCoins = [100]; // Total inserted: 100 cents

        $command = new SelectItemCommand($vendingMachine, $itemCode);
        $result = $command->execute();

        $this->assertNull($result['item']);
        $this->assertEmpty($result['change']);
        $this->assertStringContainsString(VendingMachine::ERROR_MESSAGE_NOT_ENOUGH_CHANGE, $vendingMachine->displayMessage);
    }

    public function testExecuteWhenCommandNotAllowed()
    {
        /** @var VendingMachineService $vendingMachineService */
        $vendingMachineService = app(VendingMachineService::class);

        $vendingMachine = $vendingMachineService->initDefault();

        $itemCode = '55'; // 150 cents default item (Soda)
        $vendingMachine->userMoneyManager->insertedCoins = [100, 100, 50];

        $command = new SelectItemCommand($vendingMachine, $itemCode, false);
        $result = $command->execute();

        $this->assertNull($result['item']);
        $this->assertEmpty($result['change']);
    }

    public function testExecuteNotEnoughChange()
    {
        /** @var VendingMachineService $vendingMachineService */
        $vendingMachineService = app(VendingMachineService::class);

        $vendingMachine = $vendingMachineService->initDefault();
        $vendingMachine->setState(new HasMoneyState($vendingMachine));

        $vendingMachine->inventory->updateInventory([
            '70' => ['name' => 'Chips', 'price' => 88.5, 'count' => 10], // won't have 1.5 cents to give back
        ]);

        $itemCode = '70';
        $vendingMachine->userMoneyManager->insertedCoins = [100];

        $command = new SelectItemCommand($vendingMachine, $itemCode);
        $result = $command->execute();

        $this->assertNull($result['item']);
        $this->assertStringContainsString(VendingMachine::ERROR_MESSAGE_NOT_ENOUGH_CHANGE, $vendingMachine->getDisplayMessage());
    }

    public function testExecuteWithNoChangeNeeded()
    {
        /** @var VendingMachineService $vendingMachineService */
        $vendingMachineService = app(VendingMachineService::class);

        $vendingMachine = $vendingMachineService->initDefault();

        $itemCode = '65'; // 120 cents default item (Juice)
        $vendingMachine->userMoneyManager->insertedCoins = [100, 20];

        $command = new SelectItemCommand($vendingMachine, $itemCode);
        $result = $command->execute();

        $this->assertNotNull($result['item']);
        $this->assertSame(120.0, $result['item']->getPrice());
        $this->assertSame('Juice', $result['item']->getName());
        $this->assertEmpty($result['change']);
    }

    public function testExecuteWithInvalidItemCode()
    {
        /** @var VendingMachineService $vendingMachineService */
        $vendingMachineService = app(VendingMachineService::class);

        $vendingMachine = $vendingMachineService->initDefault();

        $itemCode = '99999';
        $vendingMachine->userMoneyManager->insertedCoins = [100, 100];

        $command = new SelectItemCommand($vendingMachine, $itemCode);
        $result = $command->execute();

        $this->assertNull($result['item']);
        $this->assertEmpty($result['change']);
        $this->assertStringContainsString(VendingMachine::ERROR_MESSAGE_CODE_NOT_SET, $vendingMachine->displayMessage);
    }
}
