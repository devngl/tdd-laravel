<?php

declare(strict_types = 1);

namespace App;

use App\Billing\PaymentGateway;
use Illuminate\Support\Collection;

final class Reservation
{
    /** @var Collection|Ticket[] */
    private Collection $tickets;
    private string $email;

    public function __construct(Collection $tickets, string $email)
    {
        $this->tickets = $tickets;
        $this->email   = $email;
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

    public function complete(PaymentGateway $paymentGateway, string $paymentToken): Order
    {
        $paymentGateway->charge($this->totalCost(), $paymentToken);

        return Order::forTickets($this->tickets(), $this->email(), $this->totalCost());
    }

    public function tickets(): Collection
    {
        return $this->tickets;
    }

    public function email()
    {
        return $this->email;
    }
}
