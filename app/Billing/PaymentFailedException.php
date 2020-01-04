<?php

declare(strict_types = 1);

namespace App\Billing;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class PaymentFailedException extends HttpException
{
    protected int $statusCode = 422;
}
