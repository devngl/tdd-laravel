<?php

declare(strict_types = 1);

namespace Tests\Unit\app\Billing;

use App\Billing\StripePaymentGateway;
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
}
