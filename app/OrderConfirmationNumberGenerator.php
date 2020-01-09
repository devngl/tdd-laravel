<?php

namespace App;

interface OrderConfirmationNumberGenerator
{
    public function generate(): string;
}
