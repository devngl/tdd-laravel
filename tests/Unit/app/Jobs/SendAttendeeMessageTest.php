<?php

declare(strict_types = 1);

namespace Tests\Feature\Backstage;

use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\Helpers\ConcertFactory;
use Tests\Helpers\OrderFactory;
use Tests\TestCase;

final class SendAttendeeMessageTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_sends_the_message_to_all_concert_attendees(): void
    {
        Mail::fake();

        $concert      = ConcertFactory::createPublished();
        $otherConcert = ConcertFactory::createPublished();

        $message = AttendeeMessage::create([
            'concert_id' => $concert->id,
            'subject'    => 'My subject',
            'message'    => 'My message',
        ]);

        $orderA               = OrderFactory::createForConcert($concert, ['email' => 'angel@test.com']);
        $orderForOtherConcert = OrderFactory::createForConcert($otherConcert, ['email' => 'peter@test.com']);
        $orderB               = OrderFactory::createForConcert($concert, ['email' => 'joan@test.com']);
        $orderC               = OrderFactory::createForConcert($concert, ['email' => 'mike@test.com']);

        SendAttendeeMessage::dispatch($message);

        Mail::assertQueued(AttendeeMessageEmail::class,
            fn(AttendeeMessageEmail $mail) => $mail->hasTo('angel@test.com') && $mail->attendeeMessage->is($message));

        Mail::assertQueued(AttendeeMessageEmail::class,
            fn(AttendeeMessageEmail $mail) => $mail->hasTo('joan@test.com') && $mail->attendeeMessage->is($message));

        Mail::assertQueued(AttendeeMessageEmail::class,
            fn(AttendeeMessageEmail $mail) => $mail->hasTo('mike@test.com') && $mail->attendeeMessage->is($message));

        Mail::assertNotQueued(AttendeeMessageEmail::class,
            fn(AttendeeMessageEmail $mail) => $mail->hasTo('peter@test.com'));
    }
}
