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
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }

    private function getPaymentGateway(): FakePaymentGateway
    {
        return new FakePaymentGateway();
    }
}
