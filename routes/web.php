<?php

Route::get('/concerts/{concert}', 'ConcertsController@show');

Route::post('/concerts/{concert}/orders', 'ConcertOrdersController@store');
