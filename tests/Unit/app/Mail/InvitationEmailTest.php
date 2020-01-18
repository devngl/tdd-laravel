<?php

namespace Tests\Unit\app\Mail;


use App\Invitation;
use App\Mail\InvitationEmail;
use Tests\TestCase;

class InvitationEmailTest extends TestCase
{
    /** @test */
    public function email_contains_a_link_to_accept_the_invitation(): void
    {
        $invitation = factory(Invitation::class)->make([
            'email' => 'trustworthy@mail.com',
            'code'  => 'TESTCODE1234',
        ]);

        $email = new InvitationEmail($invitation);

        $this->assertStringContainsString(url('/invitations/TESTCODE1234'), $email->render());
    }
    /** @test */
    public function email_has_the_correct_subject(): void
    {
        $invitation = factory(Invitation::class)->make();

        $email = new InvitationEmail($invitation);

        $this->assertEquals($email->build()->subject, "You're invited to join TicketBeast");
    }
}
