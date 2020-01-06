<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\CannotPurchaseUnpublishedConcerts;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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
    public function customer_can_purchase_published_concert_tickets(): void
    {
        $this->withoutExceptionHandling();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 3250,
        ])->addTickets(3);

        $storeResponse = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $storeResponse->assertStatus(JsonResponse::HTTP_CREATED);

        $storeResponse->assertJson([
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'amount'          => 9750,
        ]);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ordersFor('john@example.com')->first()->ticketQuantity());
    }

    /** @test */
    public function email_is_required_to_purchase_tickets(): void
    {
        /** @var Concert $concert */
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
        /** @var Concert $concert */
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
        /** @var Concert $concert */
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
        /** @var Concert $concert */
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
        /** @var Concert $concert */
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 3250,
        ])->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => 'invalid-payment-token',
        ]);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_purchase_tickets_to_an_unpublished_concert(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->states('unpublished')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertEquals(CannotPurchaseUnpublishedConcerts::class, $response->decodeResponseJson()['exception']);
        $response->assertStatus(Response::HTTP_PRECONDITION_FAILED);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test */
    public function cannot_purchase_more_tickets_than_remain(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase(): void
    {
        $this->withoutExceptionHandling();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 1000,
        ])->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function (PaymentGateway $paymentGateway) use ($concert) {
            $this->expectException(NotEnoughTicketsException::class);
            try {
                $this->orderTickets($concert, [
                    'email'           => 'personB@example.com',
                    'ticket_quantity' => 1,
                    'payment_token'   => $paymentGateway->getValidTestToken(),
                ]);
            } catch (NotEnoughTicketsException $exception) {
                $this->assertFalse($concert->hasOrderFor('personB@example.com'));
                $this->assertEquals(0, $paymentGateway->totalCharges());
                throw $exception;
            }
        });

        $this->orderTickets($concert, [
            'email'           => 'personA@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);
        $this->assertEquals(3000, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('personA@example.com'));
        $this->assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
    }

    private function orderTickets($concert, array $params): TestResponse
    {
        return $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }
}
