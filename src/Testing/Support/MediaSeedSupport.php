<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing\Support;

use Illuminate\Http\Testing\File;
use Illuminate\Http\Testing\FileFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RedeyeVentures\GeoPattern\GeoPattern;

class MediaSeedSupport
{
    /**
     * Generate random avatar url.
     */
    public static function randomAvatarUrl(int $width = 100): string
    {
        $query = \http_build_query([
            'u' => Str::random(),
        ]);

        return "https://i.pravatar.cc/{$width}?{$query}";
    }

    /**
     * Generate random image url.
     */
    public static function randomImageUrl(int $width = 800, int $height = 600): string
    {
        return "https://loremflickr.com/{$width}/{$height}";
    }

    /**
     * Generate random image url from keywords.
     *
     * @param array<int, string> $keywords
     */
    public static function imageUrl(array $keywords): string
    {
        return 'https://loremflickr.com/800/600/' . \implode(',', $keywords) . '/all';
    }

    /**
     * Generate random svg.
     */
    public static function randomSvg(): File
    {
        $randomName = Str::random();

        return UploadedFile::fake()->createWithContent("{$randomName}.svg", (new GeoPattern())->toSVG());
    }

    /**
     * Create fake image.
     *
     * @param 'bmp'|'gif'|'jpeg'|'png'|'wbmp'|'webp' $extension
     */
    public static function fakeImage(string $extension = 'webp', int $width = 10, int $height = 10): File
    {
        $randomName = Str::random();

        return UploadedFile::fake()->image("{$randomName}.{$extension}", $width, $height);
    }

    /**
     * Create fake svg.
     */
    public static function fakeSvg(int $width = 10, int $height = 10): File
    {
        $randomName = Str::random();

        return (new FileFactory())->createWithContent("{$randomName}.svg", "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$width}\" height=\"{$height}\"/>");
    }
}
