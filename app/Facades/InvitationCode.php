<?php

declare(strict_types = 1);

namespace App\Facades;

use App\InvitationCodeGenerator;
use Illuminate\Support\Facades\Facade;

class InvitationCode extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return InvitationCodeGenerator::class;
    }
}
