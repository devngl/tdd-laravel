<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function cancel(): void
    {
        /** @var Ticket $ticket */
        foreach ($this->tickets()->get() as $ticket) {
            $ticket->release();
        }

        $this->delete();
    }
}

