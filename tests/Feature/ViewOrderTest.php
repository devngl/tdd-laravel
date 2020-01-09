<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

final class ViewOrderTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    /** @test */
    public function user_can_view_their_order_confirmation(): void
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->state('published')->create([]);
        $order   = factory(Order::class)->create([
            'confirmation_number' => 'ORDER_CONFIRMATION_1234',
            'card_last_four'      => '1881',
        ]);
        $ticketA  = factory(Ticket::class)->create([
            'concert_id' => $concert->getKey(),
            'order_id'   => $order->getKey(),
        ]);
        $ticketB  = factory(Ticket::class)->create([
            'concert_id' => $concert->getKey(),
            'order_id'   => $order->getKey(),
        ]);

        // Visit the order confirmation page
        $response = $this->get('/orders/ORDER_CONFIRMATION_1234');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewHas('order', $order);
        $response->assertSee('ORDER_CONFIRMATION_1234');
        $response->assertSee(sprintf('$%s', number_format($order->amount / 100, 2)));
        $response->assertSee('**** **** **** 1881');
        $response->assertSeeInOrder([$ticketA->code, $ticketB->code]);

        $response->assertSee($concert->title);
        $response->assertSee($concert->subtitle);
        $response->assertSee($concert->ticket_price);
        $response->assertSee($concert->venue);
        $response->assertSee($concert->venue_address);
        $response->assertSee($concert->city);
        $response->assertSee($concert->state);
        $response->assertSee($concert->zip);
        $response->assertSee($concert->additional_information);

        $response->assertSee($concert->date->format('Y-m-d H:i'));
    }
}
