<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\Inventory;
use App\Helpers\ChangeCalculatorHelper;
use App\Models\Item;

class InventoryTest extends TestCase
{
    private Inventory $inventory;

    protected function setUp(): void
    {
        $changeCalculator = $this->createMock(ChangeCalculatorHelper::class);
        $this->inventory = new Inventory($changeCalculator);

        $items = [
            1 => ['name' => 'Water', 'count' => 10, 'price' => 65],
            2 => ['name' => 'Juice', 'count' => 5, 'price' => 100],
            3 => ['name' => 'Soda', 'count' => 2, 'price' => 150],
        ];
        $coins = [5 => 10, 10 => 5, 25 => 5, 100 => 2];

        $this->inventory->updateInventory($items, $coins);
    }

    public function testShowItem()
    {
        $item = $this->inventory->showItem(1);
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals('Water', $item->getName());
        $this->assertEquals(65, $item->getPrice());
        $this->assertNull($this->inventory->showItem(99)); // Non-existent item code
    }

    public function testDecrementItem()
    {
        $this->assertTrue($this->inventory->decrementItem(1));
        $this->assertEquals(9, $this->inventory->items[1]['count']);
        $this->assertTrue($this->inventory->decrementItem(1));
        $this->assertEquals(8, $this->inventory->items[1]['count']);
        $this->inventory->items[3]['count'] = 0;
        $this->assertFalse($this->inventory->decrementItem(3));
    }

    public function testAddCoins()
    {
        $this->inventory->addCoins([10, 25, 10]);
        $this->assertEquals(7, $this->inventory->coins[10]);
        $this->assertEquals(6, $this->inventory->coins[25]);
    }

    public function testRemoveCoins()
    {
        $this->inventory->removeCoins([5, 10, 25]);
        $this->assertEquals(9, $this->inventory->coins[5]);
        $this->assertEquals(4, $this->inventory->coins[10]);
        $this->assertEquals(4, $this->inventory->coins[25]);
        $this->inventory->removeCoins([25, 25, 25, 25, 25, 25]);
        $this->assertEquals(0, $this->inventory->coins[25]);
    }

    public function testCalculateChange()
    {
        $changeCalculator = $this->createMock(ChangeCalculatorHelper::class);
        $changeCalculator->method('calculateOptimalChange')
            ->willReturn([25, 10, 5]);

        $inventory = new Inventory($changeCalculator);
        $inventory->updateInventory([], [25 => 5, 10 => 5, 5 => 5]);
        $result = $inventory->calculateChange(40);
        $this->assertEquals([25, 10, 5], $result);
    }

    public function testUpdateInventory()
    {
        $this->inventory->updateInventory([4 => ['name' => 'Energy Drink', 'count' => 20, 'price' => 200]],[25 => 8, 50 => 3]);

        $this->assertArrayHasKey(4, $this->inventory->items);
        $this->assertEquals('Energy Drink', $this->inventory->items[4]['item']->getName());
        $this->assertEquals(200, $this->inventory->items[4]['item']->getPrice());
        $this->assertEquals(20, $this->inventory->items[4]['count']);

        $this->assertEquals(8, $this->inventory->coins[25]);
        $this->assertEquals(3, $this->inventory->coins[50]);

        $this->assertEquals(10, $this->inventory->items[1]['count']);
        $this->assertEquals(5, $this->inventory->coins[10]);
    }
}
