<?php

declare(strict_types = 1);

namespace Tests\Feature\Backstage;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Helpers\ConcertFactory;
use Tests\Helpers\OrderFactory;
use Tests\TestCase;

final class ViewPublishedConcertOrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_promoter_can_view_the_orders_of_their_own_published_concert(): void
    {
        $this->withoutExceptionHandling();
        $user    = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published-concert-orders.index');
        $this->assertTrue($response->viewData('concert')->is($concert));
    }

    /** @test */
    public function a_promoter_can_view_the_10_most_recent_orders_for_their_concert(): void
    {
        $this->withoutExceptionHandling();
        $user    = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $orderFrom11DaysAgo = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('11 days ago')]);
        $orderFrom10DaysAgo = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('10 days ago')]);
        $orderFrom9DaysAgo  = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('9 days ago')]);
        $orderFrom8DaysAgo  = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('8 days ago')]);
        $orderFrom7DaysAgo  = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('7 days ago')]);
        $orderFrom6DaysAgo  = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('6 days ago')]);
        $orderFrom5DaysAgo  = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('5 days ago')]);
        $orderFrom4DaysAgo  = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('4 days ago')]);
        $orderFrom3DaysAgo  = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('3 days ago')]);
        $orderFrom2DaysAgo  = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('2 days ago')]);
        $orderFrom1DayAgo   = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('1 days ago')]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->viewData('orders')->assertNotContains($orderFrom11DaysAgo);
        $response->viewData('orders')->assertEquals([
            $orderFrom1DayAgo,
            $orderFrom2DaysAgo,
            $orderFrom3DaysAgo,
            $orderFrom4DaysAgo,
            $orderFrom5DaysAgo,
            $orderFrom6DaysAgo,
            $orderFrom7DaysAgo,
            $orderFrom8DaysAgo,
            $orderFrom9DaysAgo,
            $orderFrom10DaysAgo,
        ]);
    }

    /** @test */
    public function a_promoter_cannot_view_the_orders_of_unpublished_concerts(): void
    {
        $user    = factory(User::class)->create();
        $concert = ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    public function a_promoter_cannot_view_the_orders_of_another_published_concert(): void
    {
        $user      = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert   = ConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    public function a_guest_cannot_view_the_orders_of_any_published_concert(): void
    {
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertRedirect('/login');
    }
}
