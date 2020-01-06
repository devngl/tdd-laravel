<?php

declare(strict_types = 1);

namespace App\Billing;

use Illuminate\Http\Response;
use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;

final class StripePaymentGateway implements PaymentGateway
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge(int $amount, string $token): void
    {
        try {
            Charge::create([
                'amount'   => $amount,
                'currency' => 'eur',
                'source'   => $token,
            ], ['api_key' => $this->apiKey]);
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
