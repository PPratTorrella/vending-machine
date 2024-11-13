<?php

namespace Models;

use App\Factories\VendingMachineStateFactory;
use App\Helpers\ChangeCalculatorHelper;
use App\Models\Item;
use App\Models\VendingMachine;
use App\Services\Inventory;
use App\States\Concrete\HasMoneyState;
use App\States\Concrete\IdleState;
use Exception;
use Mockery;
use Tests\TestCase;

class vendingMachineTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public static function getDefaultInventory(): array
    {
        return [
            'items' => [
                50 => ['name' => 'Water', 'count' => 10, 'price' => 65],
            ],
            'coins' => [
                10 => 20,
                25 => 30,
            ]
        ];
    }

    public function test_vending_machine_gets_item_ok()
    {
        $vendingMachine = app(VendingMachine::class);

        $inventory = $this->getDefaultInventory();
        $vendingMachine->service($inventory['items'], $inventory['coins']);

        $vendingMachine->insertCoin(100);
        $this->assertInstanceOf(HasMoneyState::class, $vendingMachine->state);
        $vendingMachine->insertCoin(25);

        $coins = $vendingMachine->getInsertedCoins();
        $this->assertEquals([100, 25], $coins);

        $inventoryBefore = $vendingMachine->getInventory();

        $return = $vendingMachine->selectItem(50);
        $this->assertEquals('Water', $return['item']->name);
        $this->assertEquals([25, 25, 10], $return['coins'], 'Should return optimal combination for 60 cents');
        $this->assertInstanceOf(IdleState::class, $vendingMachine->state);

        $askReturnAGain = $vendingMachine->returnCoins();
        $this->assertEmpty($askReturnAGain);
        $this->assertInstanceOf(IdleState::class, $vendingMachine->state);

        $inventoryAfter = $vendingMachine->getInventory();
        $this->assertEquals(9, $inventoryAfter['items'][50]['count']);

        $this->assertEquals([10 => 20, 25 => 30], $inventoryBefore['coins']);
        $this->assertEquals([10 => 19, 25 => 29, 100 => 1], $inventoryAfter['coins'], 'Should have added 100 + 25 coins and removed 25 + 10 coins');
    }

    public function test_vending_machine_returns_money_ok()
    {
        $vendingMachine = app(VendingMachine::class);

        $inventory = $this->getDefaultInventory();
        $vendingMachine->service($inventory['items'], $inventory['coins']);

        $vendingMachine->insertCoin(100);
        $coins = $vendingMachine->getInsertedCoins();
        $this->assertContains(100, $coins);

        $vendingMachine->insertCoin(25);
        $coins = $vendingMachine->getInsertedCoins();
        $this->assertContains(25, $coins);
        $this->assertEquals([100, 25], $coins);

        $returnedCoins = $vendingMachine->returnCoins();
        foreach ($returnedCoins as $returnedCoin) {
            $this->assertContains($returnedCoin, [100, 25]);
        }
        $this->assertEquals(125, array_sum($coins));

        $askReturnAGain = $vendingMachine->returnCoins();
        $this->assertEmpty($askReturnAGain);
        $this->assertInstanceOf(IdleState::class, $vendingMachine->state);
    }

    public function test_vending_machine_idle_not_returns_money_ok()
    {
        $vendingMachine = app(VendingMachine::class);

        $this->assertInstanceOf(IdleState::class, $vendingMachine->state);
        $askReturnAGain = $vendingMachine->returnCoins();
        $this->assertEmpty($askReturnAGain);
    }

    public function test_vending_machine_try_get_item_money_short_ok()
    {
        $vendingMachine = app(VendingMachine::class);

        $inventory = $this->getDefaultInventory();
        $vendingMachine->service($inventory['items'], $inventory['coins']);

        $this->assertInstanceOf(IdleState::class, $vendingMachine->state);
        $this->assertEquals(IdleState::DISPLAY_MESSAGE, $vendingMachine->getDisplayMessage());

        $vendingMachine->selectItem(50); // water
        $this->assertEquals(IdleState::SELECTED_ITEM_MESSAGE, $vendingMachine->getDisplayMessage());

        $vendingMachine->insertCoin(10);
        $vendingMachine->insertCoin(25);

        $return = $vendingMachine->selectItem(50); // water
        $this->assertEmpty($return['item']);
        $this->assertEmpty($return['coins']);
        $this->assertInstanceOf(HasMoneyState::class, $vendingMachine->state);
        $this->assertStringContainsString(VendingMachine::ERROR_MESSAGE_INSUFFICIENT_FUNDS, $vendingMachine->getDisplayMessage());

        $coins = $vendingMachine->returnCoins();
        $this->assertEquals(35, array_sum($coins));
        $this->assertInstanceOf(IdleState::class, $vendingMachine->state);
    }

    public function test_vending_machine_servicing_ok()
    {
        $vendingMachine = app(VendingMachine::class);

        $inventory = $this->getDefaultInventory();
        $vendingMachine->service($inventory['items'], $inventory['coins']);

        $items = [45 => ['name' => 'Cola', 'count' => 5, 'price' => 95]];
        $coins = [10 => 10];
        $vendingMachine->service($items, $coins);

        /** for the IDE
         * @var array{
         *     items: array<int, array{ item: Item, count: int }>,
         *     coins: array<float, int>
         * } $inventoryAfter
         */
        $inventoryAfter = $vendingMachine->getInventory();

        $this->assertEquals([10 => 10, 25 => 30], $inventoryAfter['coins'], 'Should have 10 coins of 10 and 30 coins of 25 exactly');

        $this->assertEquals([50, 45], array_keys($inventoryAfter['items']), 'Should have items in code 50 and 45 and none else');

        $this->assertInstanceOf(Item::class, $inventoryAfter['items'][45]['item']);
        $this->assertEquals(5, $inventoryAfter['items'][45]['count']);
        $this->assertEquals(95, $inventoryAfter['items'][45]['item']->price);
        $this->assertEquals('Cola', $inventoryAfter['items'][45]['item']->name);

        $this->assertInstanceOf(Item::class, $inventoryAfter['items'][50]['item']);
        $this->assertEquals(10, $inventoryAfter['items'][50]['count']);
        $this->assertEquals(65, $inventoryAfter['items'][50]['item']->price);
        $this->assertEquals('Water', $inventoryAfter['items'][50]['item']->name);
    }

    public function test_vending_machine_transaction_rollback_on_failure()
    {
        $vendingMachine = app(VendingMachine::class);

        // mock inventory to fail on adding coins
        $inventoryMock = Mockery::mock(Inventory::class)->makePartial();
        $changeCalculator = app(ChangeCalculatorHelper::class);
        $inventoryMock->__construct($changeCalculator);
        $inventoryMock->items = [50 => ['item' => new Item('Water', 50), 'count' => 10]];
        $inventoryMock->coins = [10 => 20, 25 => 30, 100 => 5];
        $inventoryMock->shouldReceive('addCoins')->andThrow(new Exception('Simulated failure on adding coins'));
        $vendingMachine->inventory = $inventoryMock;

        $vendingMachine->insertCoin(100);
        $vendingMachine->insertCoin(25);
        $this->assertInstanceOf(HasMoneyState::class, $vendingMachine->state);

        $inventoryBefore = $vendingMachine->getInventory();
        $originalUserCoins = $vendingMachine->userMoneyManager->getInsertedCoins();

        $vendingMachine->selectItem(50);

        $inventoryAfter = $vendingMachine->getInventory();
        $this->assertEquals($inventoryBefore['items'][50]['count'], $inventoryAfter['items'][50]['count'], 'Item count should be rolled back');
        $this->assertEquals($inventoryBefore['coins'], $inventoryAfter['coins'], 'Coins in inventory should be rolled back');
        $this->assertEquals($originalUserCoins, $vendingMachine->userMoneyManager->getInsertedCoins(), 'User coins should be rolled back');
        $this->assertEquals(VendingMachine::ERROR_MESSAGE_SELECT_ITEM, $vendingMachine->getDisplayMessage());
    }
}
