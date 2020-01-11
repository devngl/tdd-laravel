<?php

Route::get('/concerts/{concert}', 'ConcertsController@show');
Route::post('/concerts/{concert}/orders', 'ConcertOrdersController@store');
Route::get('/orders/{confirmationNumber}', 'OrdersController@show');

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('auth.show-login');
Route::post('/login', 'Auth\LoginController@login')->name('auth.login');
Route::post('/logout', 'Auth\LoginController@logout')->name('auth.logout');

Route::get('/backstage/concerts/new', 'Backstage\ConcertsController@create');
