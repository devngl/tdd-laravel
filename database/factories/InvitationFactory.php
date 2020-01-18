<?php

/** @var Factory $factory */

use App\Invitation;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Invitation::class, static function (Faker $faker) {
    return [
        'email' => 'mail@ok.com',
        'code'  => 'random-code',
    ];
});
