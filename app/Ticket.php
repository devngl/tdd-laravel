<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    public function scopeAvailable(Builder $query)
    {
        return $query->whereNull('order_id');
    }

    public function release() :void
    {
        $this->update(['order_id' => null]);
    }
}
