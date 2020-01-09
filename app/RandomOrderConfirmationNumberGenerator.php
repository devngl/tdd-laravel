<?php

declare(strict_types = 1);

namespace App;

final class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator
{
    public function generate(): string
    {
        $length = 24;
        $pool   = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
}
