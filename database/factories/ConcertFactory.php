<?php

/** @var Factory $factory */

use App\Concert;
use App\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Concert::class, static function (Faker $faker) {
    return [
        'user_id'                => fn() => factory(User::class)->create()->id,
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
