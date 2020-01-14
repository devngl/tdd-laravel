<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date', 'created_at', 'updated_at'];

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'tickets');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasOrderFor(string $email): Bool
    {
        return $this->orders()->where('email', $email)->exists();
    }

    public function ordersFor(string $email)
    {
        return $this->orders()->where('email', $email)->get();
    }

    public function addTickets(int $quantity): Concert
    {
        foreach (range(1, $quantity) as $i) {
            $this->tickets()->create([]);
        }

        return $this;
    }

    public function ticketsRemaining(): int
    {
        return $this->tickets()->available()->count();
    }

    public function findTickets(int $ticketQuantity): Collection
    {
        /** @var Collection $tickets */
        $tickets = $this->tickets()->available()->take($ticketQuantity)->get();

        if ($tickets->count() < $ticketQuantity) {
            throw new NotEnoughTicketsException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                'Not enough tickets available for current order.');
        }

        return $tickets;
    }

    public function reserveTickets(int $quantity, string $email): Reservation
    {
        return new Reservation($this->findTickets($quantity)->each(fn(Ticket $ticket) => $ticket->reserve()), $email);
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    public function publish(): void
    {
        $this->update(['published_at' => $this->freshTimestamp()]);
        $this->addTickets($this->ticket_quantity);
    }
}
