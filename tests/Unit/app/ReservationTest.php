<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\Concert;
use App\Reservation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReservationTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    /** @test */
    public function calculating_the_total_cost(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create([
            'ticket_price' => 1200,
        ])->addTickets(3);

        $tickets = $concert->findTickets(3);
        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
    }
}
