<?php

Route::get('/concerts/{concert}', 'ConcertsController@show')->name('concerts.show');
Route::post('/concerts/{concert}/orders', 'ConcertOrdersController@store');
Route::get('/orders/{confirmationNumber}', 'OrdersController@show');

Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], static function () {
    Route::get('/concerts/new', 'ConcertsController@create')->name('backstage.concerts.new');
    Route::get('/concerts', 'ConcertsController@index')->name('backstage.concerts.index');
    Route::post('/concerts', 'ConcertsController@store');
    Route::get('/concerts/{concert}/edit', 'ConcertsController@edit')->name('backstage.concerts.edit');
    Route::patch('/concerts/{concert}', 'ConcertsController@update')->name('backstage.concerts.update');
});
