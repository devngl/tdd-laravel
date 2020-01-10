<?php

namespace App;

use App\Facades\TicketCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $guarded = [];

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    public function scopeAvailable(Builder $query)
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function release(): void
    {
        $this->update(['reserved_at' => null]);
    }

    public function getPriceAttribute(): int
    {
        return $this->concert->ticket_price;
    }

    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }

    public function claimFor(Order $order): void
    {
        $this->code = TicketCode::generateFor($this);
        $order->tickets()->save($this);
    }
}
