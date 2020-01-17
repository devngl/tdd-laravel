<?php

namespace App;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendeeMessage extends Model
{
    protected $guarded = [];

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    public function orders()
    {
        return $this->concert->orders();
    }

    public function withChunkedRecipients(int $chunkSize, Closure $callback): void
    {
        $this->orders()->chunk($chunkSize, fn($orders) => $callback($orders->pluck('email')));
    }
}
