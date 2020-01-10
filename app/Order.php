<?php

namespace App;

use App\Billing\Charge;
use App\Facades\OrderConfirmationNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Order extends Model
{
    protected $guarded = [];

    public static function forTickets(Collection $tickets, string $email, Charge $charge): Order
    {
        /** @var self $order */
        $order = self::create([
            'email'               => $email,
            'amount'              => $charge->amount(),
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'card_last_four'      => $charge->cardLastFour(),
        ]);

        $tickets->each->claimFor($order);

        return $order;
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public static function findByConfirmationNumber(string $confirmationNumber): self
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    public function ticketQuantity(): int
    {
        return $this->tickets()->count();
    }

    public function toArray()
    {
        return [
            'email'               => $this->email,
            'amount'              => $this->amount,
            'confirmation_number' => $this->confirmation_number,
            'tickets'             => $this->tickets->map(function (Ticket $ticket) {
                return ['code' => $ticket->code];
            })->all(),
        ];
    }
}

