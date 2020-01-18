<?php

namespace App\Listeners;

use App\Events\ConcertAdded;
use App\Jobs\ProcessPosterImage;

class SchedulePosterImageProcessing
{
    public function handle(ConcertAdded $event): void
    {
        if ($event->concert->hasPoster()) {
            ProcessPosterImage::dispatch($event->concert);
        }
    }
}
