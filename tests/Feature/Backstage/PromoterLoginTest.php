<?php

declare(strict_types = 1);

namespace Tests\Feature\Backstage;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

final class PromoterLoginTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function logging_in_with_valid_credentials(): void
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'jane@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/backstage/concerts');
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    /** @test */
    public function logging_in_with_invalid_credentials(): void
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'jane@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function logging_in_with_an_unregistered_account(): void
    {
        $this->withoutExceptionHandling();

        $response = $this->post('/login', [
            'email'    => 'jane@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function logging_out_the_current_user(): void
    {
        Auth::login(factory(User::class)->create());

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }
}
