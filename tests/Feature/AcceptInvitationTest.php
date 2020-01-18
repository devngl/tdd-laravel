<?php

namespace Tests\Feature;

use App\Invitation;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function viewing_an_unused_invitation(): void
    {
        $code       = 'TESTCODE123';
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => $code,
        ]);

        $response = $this->get("/invitations/{$code}");

        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($response->viewData('invitation')->is($invitation));
    }

    /** @test */
    public function viewing_a_used_invitation(): void
    {
        $code = 'TESTCODE123';
        factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create(),
            'code'    => $code,
        ]);

        $response = $this->get("/invitations/{$code}");

        $response->assertStatus(404);
    }

    /** @test */
    public function viewing_an_invitation_that_does_not_exist(): void
    {
        $response = $this->get('/invitations/TESTCODE123');
        $response->assertStatus(404);
    }

    /** @test */
    public function registering_with_a_valid_invitation_code(): void
    {
        $code       = 'TESTCODE123';
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => $code,
        ]);

        $response = $this->post('/register', [
            'email'           => 'trustworthy@guy.com',
            'password'        => 'secret',
            'invitation_code' => $code,
        ]);

        $response->assertRedirect('/backstage/concerts');

        $this->assertEquals(1, User::count());
        $user = User::first();
        $this->assertAuthenticatedAs($user);
        $this->assertEquals('trustworthy@guy.com', $user->email);
        $this->assertTrue(Hash::check('secret', $user->password));
        $this->assertTrue($invitation->fresh()->user->is($user));
    }

    /** @test */
    public function registering_with_a_used_invitation_code(): void
    {
        $code = 'USEDCODE123';
        factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create(),
            'code'    => $code,
        ]);
        $this->assertEquals(1, User::count());

        $response = $this->post('/register', [
            'email'           => 'trustworthy@guy.com',
            'password'        => 'secret',
            'invitation_code' => $code,
        ]);

        $response->assertStatus(404);
        $this->assertEquals(1, User::count());
    }

    /** @test */
    public function email_is_required(): void
    {
        $code       = 'TESTCODE123';
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => $code,
        ]);

        $response = $this->from("/invitations/{$code}")->post('/register', [
            'email'           => '',
            'password'        => 'secret',
            'invitation_code' => $code,
        ]);

        $response->assertRedirect("/invitations/{$code}");
        $response->assertSessionHasErrors('email');
        $this->assertEquals(0, User::count());
    }

    /** @test */
    public function email_must_be_a_valid_email(): void
    {
        $code = 'TESTCODE123';
        factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => $code,
        ]);

        $response = $this->from("/invitations/{$code}")->post('/register', [
            'email'           => 'not-an-email',
            'password'        => 'secret',
            'invitation_code' => $code,
        ]);

        $response->assertRedirect("/invitations/{$code}");
        $response->assertSessionHasErrors('email');
        $this->assertEquals(0, User::count());
    }

    /** @test */
    public function email_must_be_unique(): void
    {
        factory(User::class)->create(['email' => 'already_used@mail.com']);
        $this->assertEquals(1, User::count());

        $code = 'TESTCODE123';
        factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => $code,
        ]);

        $response = $this->from("/invitations/{$code}")->post('/register', [
            'email'           => 'already_used@mail.com',
            'password'        => 'secret',
            'invitation_code' => $code,
        ]);

        $response->assertRedirect("/invitations/{$code}");
        $response->assertSessionHasErrors('email');
        $this->assertEquals(1, User::count());
    }

    /** @test */
    public function password_is_required(): void
    {
        $code = 'TESTCODE123';
        factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => $code,
        ]);

        $response = $this->from("/invitations/{$code}")->post('/register', [
            'email'           => 'test@mail.com',
            'password'        => '',
            'invitation_code' => $code,
        ]);

        $response->assertRedirect("/invitations/{$code}");
        $response->assertSessionHasErrors('password');
        $this->assertEquals(0, User::count());
    }
}
