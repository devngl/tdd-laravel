<?php

declare(strict_types = 1);

namespace App\Billing;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Stripe\Charge as StripeCharge;
use Stripe\Exception\InvalidRequestException;
use Stripe\Token;

final class StripePaymentGateway implements PaymentGateway
{
    public const TEST_CARD_NUMBER = '4242424242424242';

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge(int $amount, string $token, string $destinationAccountId): Charge
    {
        try {
            $stripeCharge = StripeCharge::create([
                'amount'      => $amount,
                'currency'    => 'eur',
                'source'      => $token,
                'destination' => [
                    'account' => $destinationAccountId,
                    'amount'  => $amount * .9,
                ],
            ], ['api_key' => $this->apiKey]);

            return new Charge([
                'card_last_four' => $stripeCharge['source']['last4'],
                'amount'         => $stripeCharge['amount'],
                'destination'    => $destinationAccountId,
            ]);
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function getValidTestToken(?string $cardNumber = self::TEST_CARD_NUMBER): string
    {
        return Token::create([
            'card' => [
                'number'    => $cardNumber,
                'exp_month' => 1,
                'exp_year'  => date('Y') + 1,
                'cvc'       => '123',
            ],
        ], ['api_key' => $this->apiKey])->id;
    }

    private function lastCharge(): ?StripeCharge
    {
        return Arr::first(StripeCharge::all(
            ['limit' => 1],
            ['api_key' => $this->apiKey]
        )['data']);
    }

    private function newChargesSince(?StripeCharge $latestCharge): Collection
    {
        $newCharges = StripeCharge::all(
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

        return $this->newChargesSince($latestCharge)->map(static function ($stripeCharge) {
            return new Charge([
                'card_last_four' => $stripeCharge['source']['last4'],
                'amount'         => $stripeCharge['amount'],
            ]);
        });
    }
}
