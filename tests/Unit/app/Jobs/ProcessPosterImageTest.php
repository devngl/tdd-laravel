<?php

namespace Tests\Unit\app\Jobs;

use App\Jobs\ProcessPosterImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\ConcertFactory;
use Tests\TestCase;

class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_resize_the_poster_image_to_600px_wide(): void
    {
        Storage::fake('public');

        $filePath = 'posters/example-poster.jpg';
        Storage::disk('public')->put(
            $filePath,
            file_get_contents(base_path('tests/__fixtures__/non-optimized-image.jpg'))
        );

        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => $filePath,
        ]);

        ProcessPosterImage::dispatch($concert);

        $resizedImage = Storage::disk('public')->get($filePath);
        [$width, $height] = getimagesizefromstring($resizedImage);

        $this->assertEquals(600, $width);
        $this->assertEquals(776, $height);
    }

    /** @test */
    public function it_optimizes_the_poster_image(): void
    {
        Storage::fake('public');

        $filePath = 'posters/example-poster.jpg';
        Storage::disk('public')->put(
            $filePath,
            file_get_contents(base_path('tests/__fixtures__/small-non-optimized-image.jpg'))
        );

        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => $filePath,
        ]);

        ProcessPosterImage::dispatch($concert);

        $optimizedImageSize = Storage::disk('public')->size($filePath);
        $originalSize       = filesize(base_path('tests/__fixtures__/small-non-optimized-image.jpg'));

        $this->assertLessThan($originalSize, $optimizedImageSize);

        $optimizedSizeContent = Storage::disk('public')->get($filePath);
        $controlImageContent  = file_get_contents(base_path('tests/__fixtures__/optimized-image.jpg'));
        $this->assertEquals($optimizedSizeContent, $controlImageContent);
    }
}
