<?php

namespace Tests\Unit\App\Http\Middleware;

use App\Http\Middleware\ForceStripeAccount;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ForceStripeAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_without_stripe_account_are_forced_to_connect_with_stripe(): void
    {
        $this->be(factory(User::class)->create([
            'stripe_account_id' => null,
        ])->fresh());

        $middleware = new ForceStripeAccount;

        $response = $middleware->handle(new Request, function ($request) {
            $this->fail('Next middleware was called when it should not have been.');
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('backstage.stripe-connect.connect'), $response->getTargetUrl());
    }

    /** @test */
    public function users_with_stripe_account_can_continue(): void
    {
        $this->be(factory(User::class)->create([
            'stripe_account_id' => 'existing-stripe-account',
        ])->fresh());

        $request    = new Request;
        $middleware = new ForceStripeAccount;

        [$receivedRequest, $called] = $middleware->handle($request, fn($request) => [$request, true]);

        $this->assertTrue($called);
        $this->assertSame($request, $receivedRequest);
    }

    /** @test */
    public function middleware_is_applied_to_all_backstage_routes(): void
    {
        $routes = [
            'backstage.concerts.index',
            'backstage.concerts.new',
            'backstage.concerts.store',
            'backstage.concerts.edit',
            'backstage.concerts.update',
            'backstage.published-concerts.store',
            'backstage.published-concert-orders.index',
            'backstage.concert-messages.new',
            'backstage.concert-messages.store',
        ];

        foreach ($routes as $route) {
            $this->assertContains(
                ForceStripeAccount::class,
                Route::getRoutes()->getByName($route)->gatherMiddleware()
            );
        }
    }
}
