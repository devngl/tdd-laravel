<?php

declare(strict_types = 1);

namespace App\Billing;

use Stripe\Charge;

final class StripePaymentGateway implements PaymentGateway
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge(int $amount, string $token): void
    {
        Charge::create([
            'amount'   => $amount,
            'currency' => 'eur',
            'source'   => $token,
        ], ['api_key' => $this->apiKey]);
    }
}
