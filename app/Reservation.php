<?php

declare(strict_types = 1);

namespace App;

use Illuminate\Support\Collection;

final class Reservation
{
    /** @var Collection|Ticket[] */
    private Collection $tickets;

    public function __construct(Collection $tickets)
    {
        $this->tickets = $tickets;
    }

    public function totalCost(): int
    {
        return $this->tickets->sum('price');
    }

    public function cancel(): void
    {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }
    }
}
