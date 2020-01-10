<?php

/** @var Factory $factory */

use App\Order;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Order::class, static function (Faker $faker) {
    return [
        'email'               => $faker->email,
        'amount'              => $faker->numberBetween(5_00, 99_000_00),
        'confirmation_number' => 'ORDER_CONFIRMATION_1234',
        'card_last_four'      => '1234',
    ];
});
