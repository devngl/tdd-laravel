<?php

/** @var Factory $factory */

use App\Concert;
use App\Ticket;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;


$factory->define(Ticket::class, static function (Faker $faker) {
    return [
        'concert_id' => fn() => factory(Concert::class)->states('published')->create()->id,
        'code'       => $faker->shuffleString('ABCDEFGHIJKL'),
    ];
});

$factory->state(Ticket::class, 'reserved', static function ($faker) {
    return [
        'reserved_at' => Carbon::now(),
    ];
});

$factory->state(Ticket::class, 'unreserved', static function ($faker) {
    return [
        'reserved_at' => null,
    ];
});
