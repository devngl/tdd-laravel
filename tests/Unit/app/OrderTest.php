<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    /** @test */
    public function tickets_are_released_when_order_is_cancelled(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create()->addTickets(10);
        $order   = $concert->orderTickets('jane@example.com', 5);
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertNull($order->fresh());
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
