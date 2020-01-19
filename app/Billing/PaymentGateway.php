<?php

namespace App\Billing;

interface PaymentGateway
{
    public function charge(int $amount, string $token, string $destinationAccountId): Charge;

    public function getValidTestToken(string $cardNumber): string;
}
