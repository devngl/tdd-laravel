<?php

Route::get('/concerts/{concert}', 'ConcertsController@show');
Route::post('/concerts/{concert}/orders', 'ConcertOrdersController@store');
Route::get('/orders/{confirmationNumber}', 'OrdersController@show');

Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login');
