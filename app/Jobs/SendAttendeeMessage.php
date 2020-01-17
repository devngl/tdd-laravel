<?php

namespace App\Jobs;

use App\AttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendAttendeeMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AttendeeMessage $attendeeMessage;

    public function __construct(AttendeeMessage $attendeeMessage)
    {
        $this->attendeeMessage = $attendeeMessage;
    }

    public function handle()
    {
        $this->attendeeMessage->withChunkedRecipients(20, function (Collection $recipients) {
            return $recipients->each(function (string $recipient) {
                return Mail::to($recipient)->queue(new AttendeeMessageEmail($this->attendeeMessage));
            });
        });
    }
}
