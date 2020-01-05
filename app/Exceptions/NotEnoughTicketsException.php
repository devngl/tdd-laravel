<?php

declare(strict_types = 1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class NotEnoughTicketsException extends HttpException
{
    protected int $statusCode = 422;
}
