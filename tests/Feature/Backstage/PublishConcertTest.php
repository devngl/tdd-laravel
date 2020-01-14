<?php

declare(strict_types = 1);

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\Helpers\ConcertFactory;
use Tests\TestCase;

final class PublishConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_promoter_can_publish_their_own_concerts(): void
    {
        $this->withoutExceptionHandling();
        $user    = factory(User::class)->create();
        $concert = factory(Concert::class)->state('unpublished')->create([
            'user_id'         => $user->id,
            'ticket_quantity' => 10,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/backstage/concerts');
        $concert = $concert->fresh();
        $this->assertTrue($concert->isPublished());
        $this->assertEquals($concert->ticketsRemaining(), 10);
    }

    /** @test */
    public function a_promoter_cannot_publish_other_concerts(): void
    {
        $user      = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert   = factory(Concert::class)->state('unpublished')->create([
            'user_id'         => $otherUser->id,
            'ticket_quantity' => 10,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(404);
        $concert = $concert->fresh();
        $this->assertFalse($concert->isPublished());
        $this->assertEquals($concert->ticketsRemaining(), 0);
    }

    /** @test */
    public function guests_cannot_publish_concerts(): void
    {
        $concert = factory(Concert::class)->state('unpublished')->create([
            'user_id'         => factory(User::class)->create()->id,
            'ticket_quantity' => 10,
        ]);

        $response = $this->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/login');
        $concert = $concert->fresh();
        $this->assertFalse($concert->isPublished());
        $this->assertEquals($concert->ticketsRemaining(), 0);
    }

    /** @test */
    public function concerts_that_do_not_exists_cannot_be_published(): void
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => 999,
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function a_concert_can_only_be_published_once(): void
    {
        $user    = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id'         => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals($concert->fresh()->ticketsRemaining(), 3);
    }
}
