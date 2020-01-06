<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\Reservation;
use Tests\TestCase;

final class ReservationTest extends TestCase
{
    /** @test */
    public function calculating_the_total_cost(): void
    {
        $tickets     = collect([
            (object)['price' => 1200],
            (object)['price' => 1200],
            (object)['price' => 1200],
        ]);
        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
    }
}
