<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ConcertTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    /** @test */
    public function can_get_formatted_date(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $date = $concert->formatted_date;

        $this->assertEquals('December 1, 2016', $date);
    }

    /** @test */
    public function can_get_formatted_start_time(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2016-12-12 17:00:00'),
        ]);

        $this->assertEquals('5:00pm', $concert->formattedStartTime);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'ticket_price' => 20010,
        ]);

        $this->assertEquals($concert->ticket_price_in_dollars, 200.10);
    }

    /** @test */
    public function can_add_tickets(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 30)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 20)->create(['order_id' => null]));
        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    public function trying_to_reserve_more_tickets_than_remain_throws_an_exception(): void
    {
        $this->expectException(NotEnoughTicketsException::class);
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(10);

        try {
            $concert->reserveTickets(11, 'jane@example.com');
        } catch (Exception $exception) {
            $this->assertFalse($concert->hasOrderFor('jane@example.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());
            throw $exception;
        }
    }

    /** @test */
    public function can_reserve_available_tickets(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(3);
        $this->assertEquals(3, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2, 'john@example.com');

        $this->assertCount(2, $reservation->tickets());
        $this->assertEquals('john@example.com', $reservation->email());
        $this->assertEquals(1, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_purchased(): void
    {
        $this->expectException(NotEnoughTicketsException::class);

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(3);
        $order = factory(Order::class)->create();

        $order->tickets()->saveMany($concert->tickets->take(2));

        try {
            $concert->reserveTickets(2, 'john@example.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            throw $e;
        }
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_reserved(): void
    {
        $this->expectException(NotEnoughTicketsException::class);

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(3);
        $concert->reserveTickets(2, 'jane@example.com');

        try {
            $concert->reserveTickets(2, 'john@example.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            throw $e;
        }
    }

    /** @test */
    public function concerts_can_be_published(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('unpublished')->create([
            'ticket_quantity' => 5,
        ]);
        $this->assertFalse($concert->isPublished());
        $this->assertEquals($concert->ticketsRemaining(), 0);

        $concert->publish();

        $this->assertTrue($concert->isPublished());
        $this->assertEquals($concert->ticketsRemaining(), 5);
    }
}
