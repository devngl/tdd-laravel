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
        'additional_information' => 'Some sample additional information.',
        'date'                   => Carbon::parse('+2 weeks'),
        'venue'                  => 'The Example Theatre',
        'venue_address'          => '123 Example Lane',
        'city'                   => 'Fakeville',
        'state'                  => 'ON',
        'zip'                    => '90218',
        'ticket_price'           => 2000,
        'ticket_quantity'        => 1,
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
