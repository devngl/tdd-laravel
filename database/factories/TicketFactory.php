<?php

/** @var Factory $factory */

use App\Concert;
use App\Ticket;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Ticket::class, static function (Faker $faker) {
    return [
        'concert_id' => fn() => \factory(Concert::class)->states('published')->create()->id,
    ];
});

$factory->state(Concert::class, 'reserved', static function ($faker) {
    return [
        'reserved_at' => Carbon::parse('-2 weeks'),
    ];
});

$factory->state(Concert::class, 'unreserved', static function ($faker) {
    return [
        'reserved_at' => null,
    ];
});
