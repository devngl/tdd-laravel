<?php

declare(strict_types = 1);

namespace App\Facades;

use App\OrderConfirmationNumberGenerator;
use Illuminate\Support\Facades\Facade;

final class OrderConfirmationNumber extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OrderConfirmationNumberGenerator::class;
    }
}
