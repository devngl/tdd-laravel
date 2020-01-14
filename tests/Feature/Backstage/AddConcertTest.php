<?php

declare(strict_types = 1);

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;

final class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function promoters_can_view_the_add_concert_form(): void
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function guests_cannot_view_the_add_concert_form(): void
    {
        $response = $this->get('/backstage/concerts/new');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function adding_a_valid_concert(): void
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $validParams = $this->validParams();
        $response    = $this->actingAs($user)->post('/backstage/concerts', $validParams);

        tap(Concert::first(), function (Concert $concert) use ($response, $validParams, $user) {
            $response->assertRedirect('/backstage/concerts');

            $this->assertTrue($concert->user->is($user));

            $this->assertFalse($concert->isPublished());

            $this->assertEquals($validParams['title'], $concert->title);
            $this->assertEquals($validParams['subtitle'], $concert->subtitle);
            $this->assertEquals($validParams['additional_information'], $concert->additional_information);
            $this->assertEquals(Carbon::parse("{$validParams['date']} {$validParams['time']}"), $concert->date);
            $this->assertEquals($validParams['venue'], $concert->venue);
            $this->assertEquals($validParams['venue_address'], $concert->venue_address);
            $this->assertEquals($validParams['city'], $concert->city);
            $this->assertEquals($validParams['state'], $concert->state);
            $this->assertEquals($validParams['zip'], $concert->zip);
            $this->assertEquals($validParams['ticket_price'] * 100, $concert->ticket_price);
            $this->assertEquals($validParams['ticket_quantity'], $concert->ticket_quantity);
            $this->assertEquals(0, $concert->ticketsRemaining());
        });
    }

    /** @test */
    public function guests_cannot_a_valid_concert(): void
    {
        $response = $this->post('/backstage/concerts', $this->validParams());

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function title_is_required(): void
    {
        $user = factory(User::class)->create();

        $response = $this
            ->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['title' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('title');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function subtitle_is_optional(): void
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams(['subtitle' => '']));

        tap(Concert::first(), function (Concert $concert) use ($response) {
            $response->assertRedirect('/backstage/concerts');
            $this->assertNull($concert->subtitle);
        });
    }

    /** @test */
    public function additional_information_is_optional(): void
    {
        $this->withoutExceptionHandling();

        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'additional_information' => '',
        ]));

        tap(Concert::first(), function ($concert) use ($response) {
            $response->assertRedirect('/backstage/concerts');
            $this->assertNull($concert->additional_information);
        });
    }

    /** @test */
    public function date_is_required(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'date' => '',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('date');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function date_must_be_a_valid_date(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'date' => 'not a date',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('date');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function time_is_required(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'time' => '',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('time');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function time_must_be_a_valid_time(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'time' => 'not-a-time',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('time');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function venue_is_required(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'venue' => '',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('venue');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function venue_address_is_required(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'venue_address' => '',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('venue_address');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function city_is_required(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'city' => '',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('city');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function state_is_required(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'state' => '',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('state');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function zip_is_required(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'zip' => '',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('zip');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_price_is_required(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'ticket_price' => '',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_price_must_be_numeric(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'ticket_price' => 'not a price',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_price_must_be_at_least_5(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'ticket_price' => '4.99',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_quantity_is_required(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'ticket_quantity' => '',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_quantity_must_be_numeric(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'ticket_quantity' => 'not a number',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1(): void
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts',
            $this->validParams([
                'ticket_quantity' => '0',
            ]));
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(0, Concert::count());
    }

    private function validParams(array $overrides = []): array
    {
        return array_merge([
            'title'                  => 'No Warning',
            'subtitle'               => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years old to attend this concert.',
            'date'                   => '2017-11-18',
            'time'                   => '8:00pm',
            'venue'                  => 'The Mosh Pit',
            'venue_address'          => '123 Fake St.',
            'city'                   => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => '32.50',
            'ticket_quantity'        => '75',
        ], $overrides);
    }
}
