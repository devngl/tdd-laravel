<?php

declare(strict_types = 1);

namespace Tests\Unit\app\Billing;

use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Illuminate\Support\Arr;
use Stripe\Charge;
use Stripe\Token;
use Tests\TestCase;

/**
 * @group integration
 */
final class StripePaymentGatewayTest extends TestCase
{
    private ?Charge $lastCharge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lastCharge = $this->lastCharge();
    }

    /** @test */
    public function charges_with_a_valid_payment_token_are_successful(): void
    {
        $paymentGateway = new StripePaymentGateway((string)config('cashier.secret'));
        $paymentGateway->charge(50, $this->validToken());

        $newCharges = $this->newCharges();

        $this->assertCount(1, $newCharges);
        $this->assertEquals(50, Arr::first($newCharges)->amount);
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail(): void
    {
        $this->expectException(PaymentFailedException::class);
        $paymentGateway = new StripePaymentGateway((string)config('cashier.secret'));
        try {
            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges());
            throw $e;
        }
    }

    private function lastCharge(): ?Charge
    {
        return Arr::first(Charge::all(
            ['limit' => 1],
            ['api_key' => config('cashier.secret')]
        )['data']);
    }

    private function newCharges()
    {
        return Charge::all(
            [
                'ending_before' => $this->lastCharge ? $this->lastCharge->id : null,
            ],
            ['api_key' => config('cashier.secret')]
        )['data'];
    }

    private function validToken(): string
    {
        return Token::create([
            'card' => [
                'number'    => '4242424242424242',
                'exp_month' => 1,
                'exp_year'  => date('Y') + 1,
                'cvc'       => '123',
            ],
        ], ['api_key' => config('cashier.secret')])->id;
    }
}
