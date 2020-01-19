<?php

declare(strict_types = 1);

namespace Tests\Unit\app\Billing;

use App\Billing\StripePaymentGateway;
use Illuminate\Support\Arr;
use Stripe\Charge as StripeCharge;
use Stripe\Transfer;
use Tests\TestCase;

/**
 * @group integration
 */
final class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTest;

    private function getPaymentGateway(): StripePaymentGateway
    {
        return new StripePaymentGateway((string)config('cashier.secret'));
    }

    /** @test */
    public function ninety_percent_of_the_payment_is_transfer_to_the_destination_account(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $destinationAccountId = env('STRIPE_TEST_PROMOTER_ID');
        $paymentGateway->charge(5000, $paymentGateway->getValidTestToken(), $destinationAccountId);

        $lastStripeCharge = Arr::first(StripeCharge::all(
            ['limit' => 1],
            ['api_key' => config('cashier.secret')]
        )['data']);

        $this->assertEquals(5000, $lastStripeCharge['amount']);
        $this->assertEquals($destinationAccountId, $lastStripeCharge['destination']);

        $transfer = Transfer::retrieve($lastStripeCharge['transfer'], ['api_key' => config('cashier.secret')]);
        $this->assertEquals(4500, $transfer['amount']);
    }
}
