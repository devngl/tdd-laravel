<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful(): void
    {
        $paymentGateway = new FakePaymentGateway();
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail(): void
    {
        $this->expectException(PaymentFailedException::class);
        $paymentGateway = new FakePaymentGateway();
        $paymentGateway->charge(2500, 'invalid-payment-token');
    }

    /** @test */
    public function running_a_hook_before_the_first_charge(): void
    {
        $paymentGateway = new FakePaymentGateway();

        $timesCallbackRan = 0;
        $paymentGateway->beforeFirstCharge(function (PaymentGateway $paymentGateway) use (&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }
}
