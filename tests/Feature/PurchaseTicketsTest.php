<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\CannotPurchaseUnpublishedConcerts;
use App\Exceptions\NotEnoughTicketsException;
use App\Facades\OrderConfirmationNumber;
use App\Facades\TicketCode;
use App\Mail\OrderConfirmationEmail;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Tests\Helpers\ConcertFactory;
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
        Mail::fake();
    }

    /** @test */
    public function customer_can_purchase_published_concert_tickets(): void
    {
        $this->withoutExceptionHandling();

        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDER_CONFIRMATION_1234');
        TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');

        $user    = factory(User::class)->create(['stripe_account_id' => 'test_account_abc']);
        $concert = ConcertFactory::createPublished([
            'ticket_price'    => 3250,
            'ticket_quantity' => 3,
            'user_id'         => $user->id,
        ]);

        $storeResponse = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $storeResponse->assertStatus(JsonResponse::HTTP_CREATED);

        $storeResponse->assertJson([
            'email'               => 'john@example.com',
            'amount'              => 9750,
            'confirmation_number' => 'ORDER_CONFIRMATION_1234',
            'tickets'             => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ],
        ]);

        $this->assertEquals(9750, $this->paymentGateway->totalChargesFor('test_account_abc'));
        $this->assertTrue($concert->hasOrderFor('john@example.com'));

        $order = $concert->ordersFor('john@example.com')->first();
        $this->assertEquals(3, $order->ticketQuantity());

        Mail::assertSent(OrderConfirmationEmail::class, static function (Mailable $mail) use ($order) {
            return $mail->hasTo('john@example.com')
                && $mail->order->id === $order->id;
        });
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
        $this->assertEquals(3, $concert->fresh()->ticketsRemaining());
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
