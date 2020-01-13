<?php

Route::get('/concerts/{concert}', 'ConcertsController@show')->name('concerts.show');
Route::post('/concerts/{concert}/orders', 'ConcertOrdersController@store');
Route::get('/orders/{confirmationNumber}', 'OrdersController@show');

Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], static function () {
    Route::get('/concerts/new', 'ConcertsController@create');
    Route::get('/concerts', 'ConcertsController@index');
    Route::post('/concerts', 'ConcertsController@store');
});
