<?php

declare(strict_types = 1);

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

final class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function guests_cannot_view_promoters_concert_list(): void
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function promoters_can_only_view_a_list_of_their_concerts(): void
    {
        $this->withoutExceptionHandling();
        $user      = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concertA  = factory(Concert::class)->create(['user_id' => $user->getKey()]);
        $concertB  = factory(Concert::class)->create(['user_id' => $otherUser->getKey()]);
        $concertC  = factory(Concert::class)->create(['user_id' => $user->getKey()]);
        $concertD  = factory(Concert::class)->create(['user_id' => $user->getKey()]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);
        $response->viewData('concerts')->assertContains($concertA);
        $response->viewData('concerts')->assertContains($concertC);
        $response->viewData('concerts')->assertContains($concertD);
        $response->viewData('concerts')->assertNotContains($concertB);
        $this->assertCount(3, $response->viewData('concerts'));
    }
}
