<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    /** @test */
    public function creating_an_order_from_tickets_email_and_amount(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create()->addTickets(5);
        $this->assertEquals(5, $concert->ticketsRemaining());

        /** @var Order $order */
        $order = Order::forTickets($concert->findTickets(3), 'john@example.com', 3600);

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(2, $concert->ticketsRemaining());
    }

    /** @test */
    public function converting_to_an_array(): void
    {
        $concert = factory(Concert::class)->state('published')->create([
            'ticket_price' => 1200,
        ])->addTickets(5);
        $order   = $concert->orderTickets('jane@example.com', 5);

        $result = $order->toArray();
        $this->assertEquals([
            'email'           => 'jane@example.com',
            'ticket_quantity' => 5,
            'amount'          => 6000,
        ], $result);
    }
}
