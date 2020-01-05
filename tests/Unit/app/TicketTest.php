<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TicketTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    /** @test */
    public function a_ticket_can_be_released(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);

        $ticket = $order->tickets()->first();
        $this->assertEquals($order->getKey(), $ticket->getKey());

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
    }
}
