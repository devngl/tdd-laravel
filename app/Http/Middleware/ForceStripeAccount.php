<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ForceStripeAccount
{
    public function handle($request, Closure $next)
    {
        if (Auth::user()->stripe_account_id === null) {
            return redirect()->route('backstage.stripe-connect.connect');
        }

        return $next($request);
    }
}
