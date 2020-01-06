<?php

namespace App;

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
        $this->update(['order_id' => null]);
    }

    public function getPriceAttribute(): int
    {
        return $this->concert->ticket_price;
    }

    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }
}
