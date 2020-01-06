<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\Concert;
use App\Reservation;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

final class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function calculating_the_total_cost(): void
    {
        $tickets     = collect([
            (object)['price' => 1200],
            (object)['price' => 1200],
            (object)['price' => 1200],
        ]);
        $reservation = new Reservation($tickets, 'john@example.com');

        $this->assertEquals(3600, $reservation->totalCost());
    }

    /** @test */
    public function retrieving_the_reservation_tickets(): void
    {
        $tickets = collect([
            (object)['price' => 1200],
            (object)['price' => 1200],
            (object)['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        $this->assertEquals($tickets, $reservation->tickets());
    }

    /** @test */
    public function retrieving_the_reservation_customer_email(): void
    {
        $reservation = new Reservation(collect(), 'john@example.com');

        $this->assertEquals('john@example.com', $reservation->email());
    }

    /** @test */
    public function reserved_tickets_are_released_when_a_reservation_is_cancelled(): void
    {
//        $tickets     = collect([
//            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
//            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
//            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
//        ]);

        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        $reservation->cancel();

        /** @var Mockery\MockInterface $ticket */
        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release');
        }
    }

    /** @test */
    public function completing_a_reservation(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 1200])->addTickets(5);
        /** @var Collection|Ticket[] $tickets */
        $tickets = factory(Ticket::class, 3)->create(['concert_id' => $concert->getKey()]);

        $reservation = new Reservation($tickets, 'john@example.com');

        $order = $reservation->complete();

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
    }
}
