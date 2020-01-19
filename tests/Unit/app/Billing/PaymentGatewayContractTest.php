<?php

namespace Tests\Unit\app\Billing;

use App\Billing\Charge;
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
            $paymentGateway->charge(50, $paymentGateway->getValidTestToken('4242424242424242'),
                env('STRIPE_TEST_PROMOTER_ID'));
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(50, $newCharges->map->amount()->sum());
    }

    /** @test */
    public function can_fetch_charges_created_during_a_callback(): void
    {
        /** @var PaymentGateway $paymentGateway */
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));

        /** @var Collection $newCharges */
        $newCharges = $paymentGateway->newChargesDuring(static function (PaymentGateway $paymentGateway) {
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([5000, 4000], $newCharges->map->amount()->all());
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(static function (PaymentGateway $paymentGateway) {
            try {
                $paymentGateway->charge(2500, 'invalid-payment-token', env('STRIPE_TEST_PROMOTER_ID'));
            } catch (PaymentFailedException $e) {
                return;
            }
            $this->fail('Expected PaymentFailedException was not threw.');
        });

        $this->assertCount(0, $newCharges);
    }

    /** @test */
    public function can_get_details_about_a_successfull_charge(): void
    {
        /** @var PaymentGateway $paymentGateway */
        $paymentGateway = $this->getPaymentGateway();

        /** @var Charge $charge */
        $charge = $paymentGateway->charge(
            2500,
            $paymentGateway->getValidTestToken($paymentGateway::TEST_CARD_NUMBER),
            env('STRIPE_TEST_PROMOTER_ID')
        );

        $this->assertEquals(substr($paymentGateway::TEST_CARD_NUMBER, -4), $charge->cardLastFour());
        $this->assertEquals(2500, $charge->amount());
        $this->assertEquals(env('STRIPE_TEST_PROMOTER_ID'), $charge->destination());
    }
}
