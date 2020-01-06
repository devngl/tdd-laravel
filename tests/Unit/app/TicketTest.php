<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\Concert;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TicketTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    /** @test */
    public function a_ticket_can_be_reserved(): void
    {
        /** @var Ticket $ticket */
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function a_ticket_can_be_released(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->states('published')->create()->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);

        $ticket = $order->tickets()->first();
        $this->assertEquals($order->getKey(), $ticket->getKey());

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
    }
}
