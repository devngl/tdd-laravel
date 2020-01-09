<?php

use App\Concert;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $concert = factory(Concert::class)->states('published')->create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
            'date' => Carbon::parse('2016-12-13 08:00pm'),
            'ticket_price' => 3250,
            'additional_information' => 'This concert is 19+',
        ])->addTickets(10);

        $order   = factory(Order::class)->create([
            'confirmation_number' => 'ORDER_CONFIRMATION_1234',
            'card_last_four'      => '1881',
        ]);
        $ticketA  = factory(Ticket::class)->create([
            'code' => 'A',
            'concert_id' => $concert->getKey(),
            'order_id'   => $order->getKey(),
        ]);
        $ticketB  = factory(Ticket::class)->create([
            'code' => 'B',
            'concert_id' => $concert->getKey(),
            'order_id'   => $order->getKey(),
        ]);
    }
}
