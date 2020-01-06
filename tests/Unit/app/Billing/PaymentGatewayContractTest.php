<?php

namespace Tests\Unit\app\Billing;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use Illuminate\Support\Collection;

trait PaymentGatewayContractTest
{
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful(): void
    {
        /** @var PaymentGateway $paymentGateway */
        $paymentGateway = $this->getPaymentGateway();

        /** @var Collection $newCharges */
        $newCharges = $paymentGateway->newChargesDuring(static function (PaymentGateway $paymentGateway) {
            $paymentGateway->charge(50, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(50, $newCharges->sum());
    }

    /** @test */
    public function can_fetch_charges_created_during_a_callback(): void
    {
        /** @var PaymentGateway $paymentGateway */
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());

        /** @var Collection $newCharges */
        $newCharges = $paymentGateway->newChargesDuring(static function (PaymentGateway $paymentGateway) {
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([5000, 4000], $newCharges->all());
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(static function (PaymentGateway $paymentGateway) {
            try {
                $paymentGateway->charge(2500, 'invalid-payment-token');
            } catch (PaymentFailedException $e) {
                return;
            }
            $this->fail('Expected PaymentFailedException was not threw.');
        });

        $this->assertCount(0, $newCharges);
    }
}
