<?php

namespace App\Models;

class Item
{

    public string $name;
    public float $price;

    public function __construct($name, $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    public function __toArray(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price
        ];
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
