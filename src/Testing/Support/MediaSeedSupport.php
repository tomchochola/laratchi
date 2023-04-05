<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing\Support;

use Illuminate\Http\Testing\File;
use Illuminate\Http\Testing\FileFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
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
     * Generate random image url from keywords.
     *
     * @param array<int, string> $keywords
     */
    public static function imageUrl(array $keywords): string
    {
        $keyword = \implode(',', $keywords);

        $json = once(static function () use ($keyword): array {
            $query = \http_build_query([
                'method' => 'search',
                'keyword' => $keyword,
                'itemsperpage' => 100,
                'itemsperpage_su' => 1,
                'itemsperpage_free' => 1,
            ]);

            $response = \file_get_contents("https://www.123rfapis.com?{$query}");

            \assert($response !== false);

            $json = \json_decode($response, true);

            \assert(\is_array($json));

            return $json;
        });

        $randomIndex = \random_int(0, 99);

        $url = Arr::get($json, "0.images.123RF.image.{$randomIndex}.link_image") ?? Arr::get($json, '0.images.123RF.image.0.link_image') ?? Arr::get($json, '0.images.stockunlimited.image.0.link_image') ?? Arr::get($json, '0.images.freeimages.image.0.link_image');

        if ($url === null) {
            return static::imageUrl(['random']);
        }

        \assert(\is_string($url));

        return $url;
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
     * @param 'jpeg'|'png'|'gif'|'webp'|'wbmp'|'bmp' $extension
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
