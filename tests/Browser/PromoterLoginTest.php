<?php

namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PromoterLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function logging_in_with_valid_credentials(): void
    {
        $user = factory(User::class)->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('secret'),
        ]);

        $this->browse(static function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'secret')
                ->press('Log in')
                ->assertPathIs('/backstage/concerts');
        });
    }

    /** @test */
    public function logging_in_with_invalid_credentials(): void
    {
        $user = factory(User::class)->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('secret'),
        ]);

        $this->browse(static function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'wrong-password')
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertSee('credentials do not match');
        });
    }
}
