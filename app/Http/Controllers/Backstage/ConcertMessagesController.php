<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Jobs\SendAttendeeMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ConcertMessagesController extends Controller
{
    public function create(int $id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        return view('backstage.concert-messages.new', ['concert' => $concert]);
    }

    public function store(Request $request, int $id): RedirectResponse
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        $this->validate($request, [
            'subject' => ['required'],
            'message' => ['required'],
        ]);

        $message = $concert->attendeeMessages()->create(request(['subject', 'message']));
        SendAttendeeMessage::dispatch($message);

        return redirect()->route('backstage.concert-messages.new', $concert)
            ->with('flash', 'Your message has been sent.');
    }
}
