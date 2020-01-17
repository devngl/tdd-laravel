<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Backstage;

use Illuminate\Support\Facades\Auth;

final class PublishedConcertOrdersController
{
    public function index($concertId)
    {
        $concert = Auth::user()->concerts()->published()->findOrFail($concertId);

        return view('backstage.published-concert-orders.index', [
            'concert' => $concert,
        ]);
    }
}
