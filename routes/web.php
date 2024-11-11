<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VendingMachineController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/vending-machine', [VendingMachineController::class, 'show'])->name('vendingMachine.show');
Route::post('/vending-machine/insert-coin', [VendingMachineController::class, 'insertCoin'])->name('vendingMachine.insertCoin');
Route::post('/vending-machine/select-item', [VendingMachineController::class, 'selectItem'])->name('vendingMachine.selectItem');
Route::post('/vending-machine/service', [VendingMachineController::class, 'service'])->name('vendingMachine.service');
