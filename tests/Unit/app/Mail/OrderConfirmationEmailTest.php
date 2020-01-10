<?php

declare(strict_types = 1);

namespace Tests\Unit\app\Mail;

use App\Mail\OrderConfirmationEmail;
use App\Order;
use Tests\TestCase;

final class OrderConfirmationEmailTest extends TestCase
{
    /** @test */
    public function email_contains_a_link_to_the_order_confirmation_page(): void
    {
        $order = factory(Order::class)->make([
            'confirmation_number' => 'CONFIRMATION_ORDER_1234',
        ]);

        $email = new OrderConfirmationEmail($order);

        $this->assertStringContainsString(url('orders/CONFIRMATION_ORDER_1234'), $email->render());
    }

    /** @test */
    public function email_has_a_subject(): void
    {
        $order = factory(Order::class)->make([
            'confirmation_number' => 'CONFIRMATION_ORDER_1234',
        ]);

        $email = new OrderConfirmationEmail($order);

        $this->assertEquals('Your TicketBeast Order', $email->build()->subject);
    }
}
