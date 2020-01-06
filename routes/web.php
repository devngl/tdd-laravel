<?php

Route::get('/concerts/{concert}', 'ConcertsController@show');

Route::post('/concerts/{concert}/orders', 'ConcertOrdersController@store');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
