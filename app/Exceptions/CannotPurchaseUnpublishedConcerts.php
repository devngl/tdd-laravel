<?php

declare(strict_types = 1);

namespace App\Exceptions;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class CannotPurchaseUnpublishedConcerts extends HttpException
{
    protected int $statusCode = Response::HTTP_PRECONDITION_FAILED;
}
