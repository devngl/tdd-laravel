<?php

namespace App\Billing;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class FakePaymentGateway implements PaymentGateway
{
    private Collection $charges;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken(): string
    {
        return 'valid-token';
    }

    public function charge(int $amount, string $token): void
    {
        if ($token !== $this->getValidTestToken()) {
            throw new PaymentFailedException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Token is not valid');
        }

        $this->charges[] = $amount;
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }
}
