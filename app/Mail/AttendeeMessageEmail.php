<?php

namespace App\Mail;

use App\AttendeeMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttendeeMessageEmail extends Mailable
{
    use Queueable, SerializesModels;

    public AttendeeMessage $attendeeMessage;

    public function __construct(AttendeeMessage $attendeeMessage)
    {
        $this->attendeeMessage = $attendeeMessage;
    }

    public function build()
    {
        return $this->subject($this->attendeeMessage->subject)
            ->view('emails.attendee-message-email');
    }
}
