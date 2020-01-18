<?php

namespace App;

interface InvitationCodeGenerator
{
    public function generate(): string;
}
