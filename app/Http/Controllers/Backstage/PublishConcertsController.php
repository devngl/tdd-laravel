<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

final class PublishConcertsController
{
    public function store(Request $request): RedirectResponse
    {
        $concert = Auth::user()->concerts()->findOrFail($request->get('concert_id'));

        abort_if($concert->isPublished(), Response::HTTP_UNPROCESSABLE_ENTITY);

        $concert->publish();

        return redirect()->route('backstage.concerts.index');
    }
}
