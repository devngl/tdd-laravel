<?php

declare(strict_types = 1);

namespace Tests\Feature\Backstage;

use App\AttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Tests\TestCase;

final class AttendeeMessageEmailTest extends TestCase
{
    /** @test */
    public function email_has_the_corrent_subject_and_message(): void
    {
        $message = new AttendeeMessage([
            'subject' => 'My subject',
            'message' => <<<MSG
                A message

                with multiple and blank lines.
                MSG,
        ]);
        $email   = new AttendeeMessageEmail($message);
        $this->assertEquals('My subject', $email->build()->subject);
        $this->assertEquals(<<<MSG
                A message

                with multiple and blank lines.
                MSG, trim($email->render()));
    }
}
