<?php

declare(strict_types = 1);

namespace Tests\Unit\app;

use App\Billing\Charge;
use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    /** @test */
    public function creating_an_order_from_tickets_email_and_charge(): void
    {
        $tickets = factory(Ticket::class, 3)->create();

        $charge = new Charge([
            'amount'         => 3600,
            'card_last_four' => '1234',
        ]);

        /** @var Order $order */
        $order = Order::forTickets($tickets, 'john@example.com', $charge);

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);
    }

    /** @test */
    public function converting_to_an_array(): void
    {
        $order = factory(Order::class)->create([
            'email'               => 'jane@example.com',
            'amount'              => 6000,
            'confirmation_number' => 'ORDER_CONFIRMATION_1234',
        ]);

        $order->tickets()->saveMany(factory(Ticket::class)->times(5)->create());

        $this->assertEquals([
            'email'               => 'jane@example.com',
            'ticket_quantity'     => 5,
            'amount'              => 6000,
            'confirmation_number' => 'ORDER_CONFIRMATION_1234',
        ], $order->toArray());
    }

    /** @test */
    public function retrieving_an_order_by_confirmation_number(): void
    {
        $confirmationNumber = 'CONFIRMATION_NUMBER_123';

        $order = factory(Order::class)->create([
            'confirmation_number' => $confirmationNumber,
        ]);

        $this->assertEquals($order->id, Order::findByConfirmationNumber($confirmationNumber)->id);
    }

    /** @test */
    public function retrieving_a_non_existent_order_by_confirmation_numbers_throws_an_exception(): void
    {
        $this->expectException(ModelNotFoundException::class);

        try {
            Order::findByConfirmationNumber('NON_EXISTENT');
        } catch (ModelNotFoundException $e) {
            throw $e;
        }

        $this->fail('No matching order was found for confirmation number but an exception was not thrown.');
    }
}
