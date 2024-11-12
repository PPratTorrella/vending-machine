<?php

namespace Tests\Unit\Commands\Concrete\VendingMachine;

use App\Commands\Concrete\VendingMachine\SelectItemCommand;
use App\Services\VendingMachineService;
use Tests\TestCase;

class SelectItemCommandTest extends TestCase
{

    public function testExecuteWithSufficientFundsAndStock()
    {
        /** @var $vendingMachineService VendingMachineService */
        $vendingMachineService = app(VendingMachineService::class);

        $vendingMachine = $vendingMachineService->initDefault();

        $itemCode = '55'; // 150 cents default item
        $vendingMachine->userMoneyManager->insertedCoins = [100, 100, 50];

        $command = new SelectItemCommand($vendingMachine, $itemCode);
        $result = $command->execute();

        $this->assertNotNull($result['item']);
        $this->assertSame(150.0, $result['item']->getPrice());
        $this->assertSame('Soda', $result['item']->getName());
        $this->assertSame([100], $result['change']);
    }
}
