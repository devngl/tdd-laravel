<?php

namespace App\Jobs;

use App\Concert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;

class ProcessPosterImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Concert $concert;

    public function __construct(Concert $concert)
    {
        $this->concert = $concert;
    }

    public function handle()
    {
        $imageContents = Storage::disk('public')->get($this->concert->poster_image_path);
        $image         = Image::make($imageContents);

        $image->resize(600, null, static function (Constraint $constraint) {
            $constraint->aspectRatio();
        })->limitColors(255)->encode($image->extension, 75);

        Storage::disk('public')->put($this->concert->poster_image_path, (string)$image);
    }
}
