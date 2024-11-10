<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Item;

class ItemTest extends TestCase
{
    public function testGetName()
    {
        $item = new Item('Water', 65);
        $this->assertEquals('Water', $item->getName());
    }

    public function testGetPrice()
    {
        $item = new Item('Water', 65);
        $this->assertEquals(65, $item->getPrice());
    }

    public function testToArray()
    {
        $item = new Item('Water', 65);
        $expectedArray = [
            'name' => 'Water',
            'price' => 65,
        ];

        $this->assertEquals($expectedArray, $item->__toArray());
    }

    public function testSettersAndGetters()
    {
        $item = new Item('Juice', 100);

        $this->assertEquals('Juice', $item->getName());
        $this->assertEquals(100, $item->getPrice());
    }
}
