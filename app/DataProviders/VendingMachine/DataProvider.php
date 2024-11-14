<?php

namespace App\DataProviders\VendingMachine;

use App\Engine\Interfaces\VendingMachineInterface;
use App\Repositories\VendingMachine\Interfaces\StorageInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class DataProvider
{
    protected StorageInterface $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getVendingMachine(): VendingMachineInterface
    {
        try {
            return $this->storage->getVendingMachine();
        } catch (Exception $e) {
            Log::error("Failed to retrieve vending machine: " . $e->getMessage());
            throw new Exception("Unable to load the vending machine.");
        }
    }

    public function saveVendingMachine(VendingMachineInterface $vendingMachine): void
    {
        try {
            $this->storage->saveVendingMachine($vendingMachine);
        } catch (Exception $e) {
            Log::error("Failed to save vending machine: " . $e->getMessage());
            throw new Exception("Unable to save the vending machine.");
        }
    }

    public function initDefault(): VendingMachineInterface
    {
        try {
            return $this->storage->initDefault();
        } catch (Exception $e) {
            Log::error("Failed to init default vending machine: " . $e->getMessage());
            throw new Exception("Unable to init default vending machine.");
        }
    }
}
