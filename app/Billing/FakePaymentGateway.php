<?php

namespace App\Billing;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{
    public const TEST_CARD_NUMBER = '4242424242424242';

    private Collection $charges;
    private Collection $tokens;
    private ?Closure $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens  = collect();
        /** @noinspection PropertyInitializationFlawsInspection */
        $this->beforeFirstChargeCallback = null;
    }

    public function getValidTestToken(string $cardNumber = null): string
    {
        $token                = 'fake-tok_'.Str::random(24);
        $this->tokens[$token] = $cardNumber;

        return $token;
    }

    public function charge(int $amount, string $token, string $destinationAccountId): Charge
    {
        if ($this->beforeFirstChargeCallback !== null) {
            $callback                        = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if (!$this->tokens->has($token)) {
            throw new PaymentFailedException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Token is not valid');
        }

        return $this->charges[] = new Charge([
            'amount'         => $amount,
            'card_last_four' => substr($this->tokens[$token], -4),
            'destination'    => $destinationAccountId,
        ]);
    }

    public function totalCharges()
    {
        return $this->charges->map->amount()->sum();
    }

    public function totalChargesFor(string $accountId)
    {
        return $this->charges->filter(fn($charge) => $charge->destination() === $accountId)->map->amount()->sum();
    }

    public function beforeFirstCharge(Closure $callback): void
    {
        $this->beforeFirstChargeCallback = $callback;
    }

    public function newChargesDuring(Closure $closure): Collection
    {
        $chargesFrom = $this->charges->count();
        $closure($this);

        return $this->charges->slice($chargesFrom)->reverse()->values();
    }
}
