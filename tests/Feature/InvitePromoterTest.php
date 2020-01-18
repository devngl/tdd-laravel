<?php

namespace Tests\Feature;

use App\Facades\InvitationCode;
use App\Invitation;
use App\Mail\InvitationEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitePromoterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function inviting_a_promoter_via_the_cli(): void
    {
        Mail::fake();
        InvitationCode::shouldReceive('generate')->andReturn('TESTCODE1234');
        $this->artisan('invite-promoter', [
            'email' => 'trustworthy@mail.com',
        ]);

        $this->assertEquals(1, Invitation::count());

        $invitation = Invitation::first();
        $this->assertEquals('trustworthy@mail.com', $invitation->email);
        $this->assertEquals('TESTCODE1234', $invitation->code);
        Mail::assertSent(InvitationEmail::class, function ($mail) use ($invitation) {
            return $mail->hasTo('trustworthy@mail.com')
                && $mail->invitation->is($invitation);
        });
    }
}
