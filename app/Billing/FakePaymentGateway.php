<?php

namespace App\Billing;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class FakePaymentGateway implements PaymentGateway
{
    private Collection $charges;
    private ?Closure $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        /** @noinspection PropertyInitializationFlawsInspection */
        $this->beforeFirstChargeCallback = null;
    }

    public function getValidTestToken(): string
    {
        return 'valid-token';
    }

    public function charge(int $amount, string $token): void
    {
        if ($this->beforeFirstChargeCallback !== null) {
            $callback                        = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if ($token !== $this->getValidTestToken()) {
            throw new PaymentFailedException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Token is not valid');
        }

        $this->charges[] = $amount;
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }

    public function beforeFirstCharge(Closure $callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
}
