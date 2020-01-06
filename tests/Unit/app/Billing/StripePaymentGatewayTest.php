<?php

declare(strict_types = 1);

namespace Tests\Unit\app\Billing;

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
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful(): void
    {
        $lastCharge     = $this->lastCharge();
        $paymentGateway = new StripePaymentGateway((string)config('cashier.secret'));
        $paymentGateway->charge(50, $this->validToken());

        $newCharges = $this->newCharges($lastCharge);

        $this->assertCount(1, $newCharges);
        $this->assertEquals(50, Arr::first($newCharges)->amount);
    }

    private function lastCharge(): ?Charge
    {
        return Arr::first(Charge::all(
            ['limit' => 1],
            ['api_key' => config('cashier.secret')]
        )['data']);
    }

    private function newCharges(?Charge $lastCharge)
    {
        return Charge::all(
            [
                'ending_before' => $lastCharge ? $lastCharge->id : null,
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
