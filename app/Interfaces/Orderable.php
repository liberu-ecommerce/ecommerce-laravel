<?php

namespace App\Interfaces;

interface Orderable
{
    public function getPrice(): float;
    public function getName(): string;
}
