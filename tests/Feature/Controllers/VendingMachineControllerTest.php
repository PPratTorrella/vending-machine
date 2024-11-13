<?php

namespace Tests\Feature\Controllers;

use App\Models\Item;
use Tests\TestCase;
use App\Services\VendingMachineService;

class VendingMachineControllerTest extends TestCase
{
    protected VendingMachineService $vendingMachineService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vendingMachineService = app(VendingMachineService::class);
    }

    public function test_displays_the_vending_machine_view()
    {
        $response = $this->get(route('vendingMachine.show'));

        $response->assertStatus(200);
        $response->assertViewIs('vendingMachine.index');
        $response->assertViewHas('coinLabels');
    }

    public function test_inserts_a_valid_coin()
    {
        $response = $this->post(route('vendingMachine.insertCoin'), ['coin' => 100]);

        $response->assertRedirect(route('vendingMachine.show'));
        $response->assertSessionMissing('dispensed.coins');
    }

    public function test_handles_invalid_coin_insertion()
    {
        $response = $this->post(route('vendingMachine.insertCoin'), ['coin' => 77]);

        $response->assertRedirect(route('vendingMachine.show'));
        $response->assertSessionHas('dispensed', ['coins' => [77]]);
    }

    public function test_returns_inserted_coins()
    {
        $this->post(route('vendingMachine.insertCoin'), ['coin' => 100]);
        $this->post(route('vendingMachine.insertCoin'), ['coin' => 25]);

        $response = $this->get(route('vendingMachine.returnCoins'));

        $response->assertRedirect(route('vendingMachine.show'));
        $response->assertSessionHas('dispensed.coins', [100, 25]);
    }

    public function test_selects_an_item_successfully()
    {
        $this->post(route('vendingMachine.insertCoin'), ['coin' => 100]);
        $this->post(route('vendingMachine.insertCoin'), ['coin' => 25]);
        $this->post(route('vendingMachine.insertCoin'), ['coin' => 25]);

        $response = $this->post(route('vendingMachine.selectItem'), ['item_code' => '55']);

        $response->assertRedirect(route('vendingMachine.show'));

        $dispensed = session('dispensed');
        $this->assertNotNull($dispensed['item']);
        $this->assertInstanceOf(Item::class, $dispensed['item']);
        $this->assertEquals('Soda', $dispensed['item']->getName());
    }

    public function test_services_the_vending_machine_with_valid_data()
    {
        $items = [
            ['name' => 'Soda', 'price' => 150, 'count' => 10],
            ['name' => 'Water', 'price' => 65, 'count' => 5]
        ];
        $coins = [100 => 5, 50 => 10];

        $response = $this->post(route('vendingMachine.service'), [
            'items' => json_encode($items),
            'coins' => json_encode($coins),
        ]);

        $response->assertRedirect(route('vendingMachine.show'));
        $response->assertSessionMissing('message');
    }

    public function test_shows_error_on_invalid_service_data()
    {
        $invalidCoins = ['not-an-integer'];
        $items = [
            ['name' => 'Soda', 'price' => 150, 'count' => 10],
        ];

        $response = $this->post(route('vendingMachine.service'), [
            'items' => json_encode($items),
            'coins' => json_encode($invalidCoins),
        ]);

        $response->assertRedirect(route('vendingMachine.show'));
        $response->assertSessionHas('message', 'Invalid structure in items or coins.');
    }
}
