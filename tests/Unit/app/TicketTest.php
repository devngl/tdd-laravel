<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\Facades\TicketCode;
use App\Order;
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
        /** @var Ticket $ticket */
        $ticket = factory(Ticket::class)->states('reserved')->create();
        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function a_ticket_can_be_claim_for_an_order(): void
    {
        /** @var Order $order */
        $order = factory(Order::class)->create();
        /** @var Ticket $ticket */
        $ticket = factory(Ticket::class)->create(['code' => null]);
        TicketCode::shouldReceive('generateFor')
            ->with($ticket)
            ->andReturn('TICKETCODE1');

        $ticket->claimFor($order);

        $this->assertContains($ticket->id, $order->tickets->pluck('id'));
        $this->assertEquals('TICKETCODE1', $ticket->code);
    }
}
