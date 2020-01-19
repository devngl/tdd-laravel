<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Zttp\Zttp;

class StripeConnectController extends Controller
{
    public function connect()
    {
        return view('backstage.stripe-connect.connect');
    }

    public function authorizeRedirect()
    {
        $url = vsprintf('%s?%s', [
            'https://connect.stripe.com/oauth/authorize',
            http_build_query([
                'response_type' => 'code',
                'scope'         => 'read_write',
                'client_id'     => config('cashier.stripe.client_id'),
            ]),
        ]);

        return redirect($url);
    }

    public function redirect(Request $request)
    {
        $accessTokenResponse = Zttp::asFormParams()->post('https://connect.stripe.com/oauth/token', [
            'grant_type'    => 'authorization_code',
            'code'          => $request->get('code'),
            'client_secret' => config('cashier.secret'),
        ])->json();

        Auth::user()->update([
            'stripe_account_id'   => $accessTokenResponse['stripe_user_id'],
            'stripe_access_token' => $accessTokenResponse['access_token'],
        ]);

        return redirect()->route('backstage.concerts.index');
    }
}
