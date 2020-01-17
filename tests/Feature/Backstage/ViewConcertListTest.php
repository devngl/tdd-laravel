<?php

declare(strict_types = 1);

namespace Tests\Feature\Backstage;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Helpers\ConcertFactory;
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
        $user              = factory(User::class)->create();
        $otherUser         = factory(User::class)->create();
        $publishedConcertA = ConcertFactory::createPublished(['user_id' => $user->getKey()]);
        $publishedConcertB = ConcertFactory::createPublished(['user_id' => $otherUser->getKey()]);
        $publishedConcertC = ConcertFactory::createPublished(['user_id' => $user->getKey()]);

        $unpublishedConcertA = ConcertFactory::createUnpublished(['user_id' => $user->getKey()]);
        $unpublishedConcertB = ConcertFactory::createUnpublished(['user_id' => $otherUser->getKey()]);
        $unpublishedConcertC = ConcertFactory::createUnpublished(['user_id' => $user->getKey()]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);

        $response->viewData('publishedConcerts')->assertEquals([$publishedConcertA, $publishedConcertC]);
        $response->viewData('unpublishedConcerts')->assertEquals([$unpublishedConcertA, $unpublishedConcertC]);
    }
}
