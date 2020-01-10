<?php

declare(strict_types = 1);

namespace App;

use Hashids\Hashids;

class HashIdsTicketCodeGenerator implements TicketCodeGenerator
{
    private Hashids $hashIds;

    public function __construct(string $salt)
    {
        $this->hashIds = new Hashids($salt, 6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public function generateFor(Ticket $ticket): string
    {
        return $this->hashIds->encode($ticket->id);
    }
}
