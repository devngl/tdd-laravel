<?php

declare(strict_types = 1);

namespace Tests\Helpers;

use App\Concert;

final class ConcertFactory
{
    public static function createPublished($overrides): Concert
    {
        $concert = factory(Concert::class)->create($overrides);
        $concert->publish();

        return $concert;
    }
}