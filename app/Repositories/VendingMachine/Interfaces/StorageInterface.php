<?php

namespace App\Repositories\VendingMachine\Interfaces;

use App\Models\VendingMachine;

interface StorageInterface
{
    public function getVendingMachine(): VendingMachine;

    public function saveVendingMachine(VendingMachine $vendingMachine): void;

    public function initDefault(): VendingMachine;
}
