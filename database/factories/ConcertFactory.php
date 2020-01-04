<?php

/** @var Factory $factory */

use App\Concert;
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

$factory->define(Concert::class, static function (Faker $faker) {
    return [
        'title'                  => 'Example Band',
        'subtitle'               => 'with The Fake Openers',
        'date'                   => Carbon::parse('+2 weeks'),
        'ticket_price'           => 2000,
        'venue'                  => 'The Example Theatre',
        'venue_address'          => '123 Example Lane',
        'city'                   => 'Fakeville',
        'state'                  => 'ON',
        'zip'                    => '90218',
        'additional_information' => 'Some sample additional information.',
    ];
});

$factory->state(Concert::class, 'published', static function ($faker) {
    return [
        'published_at' => Carbon::parse('-2 weeks'),
    ];
});

$factory->state(Concert::class, 'unpublished', static function ($faker) {
    return [
        'published_at' => null,
    ];
});
