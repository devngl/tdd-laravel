<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ViewConcertListingTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_a_published_concert_listing(): void
    {
        $concert = factory(Concert::class)
            ->states('published')
            ->create([
                'title'                  => 'The Red Chord',
                'subtitle'               => 'with Animosity and Lethargy',
                'date'                   => Carbon::parse('December 13, 2016 08:00pm'),
                'ticket_price'           => 3250,
                'venue'                  => 'The Mosh Pit',
                'venue_address'          => '123 Example Lane',
                'city'                   => 'Laraville',
                'state'                  => 'ON',
                'zip'                    => '17916',
                'additional_information' => 'For tickets, call (555) 555-5555',
            ]);

        $response = $this->get('/concerts/'.$concert->id);
        $response->assertStatus(200);
        $response->assertViewIs('concerts.show');

        $response->assertSeeText('The Red Chord');
        $response->assertSeeText('with Animosity and Lethargy');
        $response->assertSeeText('December 13, 2016');
        $response->assertSeeText('8:00pm');
        $response->assertSeeText('32.50');
        $response->assertSeeText('The Mosh Pit');
        $response->assertSeeText('123 Example Lane');
        $response->assertSeeText('Laraville, ON 17916');
        $response->assertSeeText('For tickets, call (555) 555-5555');
    }

    /** @test */
    public function user_cannot_view_unpublished_concerts(): void
    {
        $concert = factory(Concert::class)->states('unpublished')->create();

        $response = $this->get('/concerts/'.$concert->id);
        $response->assertStatus(404);
    }
}
