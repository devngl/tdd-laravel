<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

final class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    private FakePaymentGateway $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    public function customer_can_purchase_concert_tickets(): void
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250,
        ]);

        $storeResponse = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);
        $storeResponse->assertStatus(JsonResponse::HTTP_CREATED);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $this->assertTrue($concert->orders->contains(static function ($order) {
            return $order->email === 'john@example.com';
        }));

        $order = $concert->orders->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets->count());
    }

    /** @test */
    public function email_is_required_to_purchase_tickets(): void
    {
        $concert = factory(Concert::class)->create();

        $storeResponse = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $storeResponse->assertJsonValidationErrors('email');
    }

    /** @test */
    public function payment_token_is_required(): void
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
        ]);

        $response->assertJsonValidationErrors('payment_token');
    }

    /** @test */
    public function ticket_quantity_is_required(): void
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'email'         => 'john@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertJsonValidationErrors('ticket_quantity');
    }

    /** @test */
    public function ticket_quantity_must_be_greater_than_zero(): void
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
            'ticket_quantity' => 0,
        ]);

        $response->assertJsonValidationErrors('ticket_quantity');
    }

    /** @test */
    public function order_is_not_created_if_payment_fails(): void
    {
        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250,
        ]);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => 'invalid-payment-token',
        ]);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNull($order);
    }

    private function orderTickets($concert, array $params): TestResponse
    {
        return $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }
}
