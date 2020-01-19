<?php

namespace Tests\Unit\app\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTest;

    /** @test */
    public function running_a_hook_before_the_first_charge(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $timesCallbackRan = 0;
        $paymentGateway->beforeFirstCharge(function (PaymentGateway $paymentGateway) use (&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_account_abc');
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_account_abc');
        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }

    /** @test */
    public function can_get_total_charges_for_specific_account(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken(), 'test_account_0001');
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_account_0002');
        $paymentGateway->charge(4000, $paymentGateway->getValidTestToken(), 'test_account_0002');

        $this->assertEquals(6500, $paymentGateway->totalChargesFor('test_account_0002'));
    }

    private function getPaymentGateway(): FakePaymentGateway
    {
        return new FakePaymentGateway();
    }
}
