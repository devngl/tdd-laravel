<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Illuminate\Support\Facades\Auth;

final class PublishedConcertOrdersController
{
    public function index($concertId)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->published()->findOrFail($concertId);

        return view('backstage.published-concert-orders.index', [
            'concert' => $concert,
            'orders'  => $concert->orders()->latest()->take(10)->get(),
        ]);
    }
}
