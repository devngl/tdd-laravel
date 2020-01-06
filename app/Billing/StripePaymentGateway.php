<?php

declare(strict_types = 1);

namespace App\Billing;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;
use Stripe\Token;

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

    public function getValidTestToken(): string
    {
        return Token::create([
            'card' => [
                'number'    => '4242424242424242',
                'exp_month' => 1,
                'exp_year'  => date('Y') + 1,
                'cvc'       => '123',
            ],
        ], ['api_key' => $this->apiKey])->id;
    }

    private function lastCharge(): ?Charge
    {
        return Arr::first(Charge::all(
            ['limit' => 1],
            ['api_key' => $this->apiKey]
        )['data']);
    }

    private function newChargesSince(?Charge $latestCharge) : Collection
    {
        $newCharges = Charge::all(
            [
                'ending_before' => $latestCharge ? $latestCharge->id : null,
            ],
            ['api_key' => config('cashier.secret')]
        )['data'];

        return collect($newCharges);
    }

    public function newChargesDuring(Closure $callable): Collection
    {
        $latestCharge = $this->lastCharge();
        $callable($this);

        return $this->newChargesSince($latestCharge)->pluck('amount');
    }
}
