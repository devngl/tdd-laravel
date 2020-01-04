<?php

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcertsControllerTest extends TestCase
{
    use DatabaseMigrations;
    use RefreshDatabase;

    /** @test */
    public function can_get_formatted_date(): void
    {
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $date = $concert->formatted_date;

        $this->assertEquals('December 1, 2016', $date);
    }

    /** @test */
    public function can_get_formatted_start_time(): void
    {
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2016-12-12 17:00:00'),
        ]);

        $this->assertEquals('5:00pm', $concert->formattedStartTime);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars(): void
    {
        $concert = factory(Concert::class)->create([
            'ticket_price' => 20010,
        ]);

        $this->assertEquals($concert->ticket_price_in_dollars, 200.10);
    }

    /** @test */
    public function can_order_concert_tickets(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create();

        $order = $concert->orderTickets('jane@example.com', 3);

        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals('3', $order->tickets()->count());
    }
}
