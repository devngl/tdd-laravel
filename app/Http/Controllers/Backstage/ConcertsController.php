<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ConcertsController extends Controller
{
    public function index()
    {
        return view('backstage.concerts.index', [
            'concerts' => Auth::user()->concerts,
        ]);
    }

    public function create()
    {
        return view('backstage.concerts.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title'           => ['required'],
            'date'            => ['required', 'date'],
            'time'            => ['required', 'date_format:g:ia'],
            'venue'           => ['required'],
            'venue_address'   => ['required'],
            'city'            => ['required'],
            'state'           => ['required'],
            'zip'             => ['required'],
            'ticket_price'    => ['required', 'numeric', 'min:5'],
            'ticket_quantity' => ['required', 'numeric', 'min:1'],
        ]);

        $concert = Auth::user()->concerts()->create([
            'title'                  => $request->get('title'),
            'subtitle'               => $request->get('subtitle'),
            'additional_information' => $request->get('additional_information'),
            'venue'                  => $request->get('venue'),
            'venue_address'          => $request->get('venue_address'),
            'city'                   => $request->get('city'),
            'state'                  => $request->get('state'),
            'zip'                    => $request->get('zip'),
            'date'                   => Carbon::parse(vsprintf('%s %s', [
                $request->get('date'),
                $request->get('time'),
            ])),
            'ticket_price'           => $request->get('ticket_price') * 100,
            'ticket_quantity'        => (int)$request->get('ticket_quantity'),
        ]);

        $concert->publish();

        return redirect()->route('backstage.concerts.index');
    }

    public function edit(Concert $concert)
    {
        abort_if(!$concert->user->is(Auth::user()), 404);
        abort_if($concert->isPublished(), 403);

        return view('backstage.concerts.edit', ['concert' => $concert]);
    }

    public function update(Concert $concert, Request $request)
    {
        abort_if(!$concert->user->is(Auth::user()), 404);
        abort_if($concert->isPublished(), 403);

        $this->validate($request, [
            'title'           => ['required'],
            'date'            => ['required', 'date'],
            'time'            => ['required', 'date_format:g:ia'],
            'venue'           => ['required'],
            'venue_address'   => ['required'],
            'city'            => ['required'],
            'state'           => ['required'],
            'zip'             => ['required'],
            'ticket_price'    => ['required', 'numeric', 'min:5'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
        ]);

        $concert->update([
            'title'                  => $request->get('title'),
            'subtitle'               => $request->get('subtitle'),
            'additional_information' => $request->get('additional_information'),
            'venue'                  => $request->get('venue'),
            'venue_address'          => $request->get('venue_address'),
            'city'                   => $request->get('city'),
            'state'                  => $request->get('state'),
            'zip'                    => $request->get('zip'),
            'date'                   => Carbon::parse(vsprintf('%s %s', [
                $request->get('date'),
                $request->get('time'),
            ])),
            'ticket_price'           => $request->get('ticket_price') * 100,
            'ticket_quantity'        => (int)$request->get('ticket_quantity'),
        ]);

        return redirect()->route('backstage.concerts.index');
    }
}
