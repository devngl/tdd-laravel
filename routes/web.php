<?php

use App\Http\Middleware\ForceStripeAccount;

Route::get('/concerts/{concert}', 'ConcertsController@show')->name('concerts.show');
Route::post('/concerts/{concert}/orders', 'ConcertOrdersController@store');
Route::get('/orders/{confirmationNumber}', 'OrdersController@show');

Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
Route::post('/register', 'Auth\RegisterController@register')->name('auth.register');

Route::get('/invitations/{code}', 'InvitationsController@show')->name('invitations.show');

Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], static function () {
    Route::group(['middleware' => ForceStripeAccount::class], static function () {
        Route::get('/concerts', 'ConcertsController@index')->name('backstage.concerts.index');
        Route::get('/concerts/new', 'ConcertsController@create')->name('backstage.concerts.new');
        Route::post('/concerts', 'ConcertsController@store')->name('backstage.concerts.store');
        Route::get('/concerts/{concert}/edit', 'ConcertsController@edit')->name('backstage.concerts.edit');
        Route::patch('/concerts/{concert}', 'ConcertsController@update')->name('backstage.concerts.update');

        Route::post('/published-concerts', 'PublishConcertsController@store')->name('backstage.published-concerts.store');
        Route::get('/published-concerts/{concertId}/orders', 'PublishedConcertOrdersController@index')->name('backstage.published-concert-orders.index');

        Route::get('/concerts/{id}/messages/new', 'ConcertMessagesController@create')->name('backstage.concert-messages.new');
        Route::post('/concerts/{id}/messages', 'ConcertMessagesController@store')->name('backstage.concert-messages.store');
    });

    Route::get('/stripe-connect/connect', 'StripeConnectController@connect')->name('backstage.stripe-connect.connect');
    Route::get('/stripe-connect/authorize', 'StripeConnectController@authorizeRedirect')->name('backstage.stripe-connect.authorize');
    Route::get('/stripe-connect/redirect', 'StripeConnectController@redirect')->name('backstage.stripe-connect.redirect');
});
