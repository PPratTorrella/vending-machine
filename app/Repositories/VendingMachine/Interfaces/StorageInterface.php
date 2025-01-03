<?php

namespace App\Repositories\VendingMachine\Interfaces;

use App\Engine\Interfaces\VendingMachineInterface;

interface StorageInterface
{
    public function getVendingMachine(): VendingMachineInterface;

    public function saveVendingMachine(VendingMachineInterface $vendingMachine): void;

    public function initDefault(): VendingMachineInterface;
}
