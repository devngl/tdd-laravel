<?php

declare(strict_types = 1);

namespace Tests\Helpers;

use App\Concert;
use App\Order;
use App\Ticket;

final class OrderFactory
{
    public static function createForConcert(Concert $concert, array $overrides = [], int $ticketQuantity = 1): Order
    {
        $order   = factory(Order::class)->create($overrides);
        $tickets = factory(Ticket::class, $ticketQuantity)->create(['concert_id' => $concert->id]);
        $order->tickets()->saveMany($tickets);

        return $order;
    }
}
