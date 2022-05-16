<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing\Support;

use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SeedSupport
{
    /**
     * Generate random avatar url.
     */
    public static function randomAvatarUrl(int $width = 100): string
    {
        $query = \http_build_query([
            'u' => Str::random(30),
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

        $query = \http_build_query([
            'method' => 'search',
            'keyword' => $keyword,
            'itemsperpage' => 100,
            'itemsperpage_su' => 1,
            'itemsperpage_free' => 1,
        ]);

        $response = \file_get_contents("https://www.123rfapis.com?{$query}");

        if ($response === false) {
            return static::randomImageUrl();
        }

        $json = \json_decode($response, true);

        if ($json === null) {
            return static::randomImageUrl();
        }

        \assert(\is_array($json));

        $randomIndex = \random_int(0, 99);

        $url = Arr::get($json, "0.images.123RF.image.{$randomIndex}.link_image") ?? Arr::get($json, '0.images.stockunlimited.image.0.link_image') ?? Arr::get($json, '0.images.freeimages.image.0.link_image');

        if ($url === null) {
            return static::randomImageUrl();
        }

        \assert(\is_string($url));

        return $url;
    }

    /**
     * Generate random image url.
     */
    public static function randomImageUrl(int $width = 100, ?int $height = null): string
    {
        $seed = Str::random(30);

        if ($height !== null) {
            return "https://picsum.photos/seed/{$seed}/{$width}/{$height}";
        }

        return "https://picsum.photos/seed/{$seed}/{$width}";
    }

    /**
     * Create fake image.
     *
     * @param 'jpeg'|'png'|'gif'|'webp'|'wbmp'|'bmp' $extension
     */
    public static function fakeImage(string $extension = 'webp', int $width = 10, int $height = 10): File
    {
        $randomName = Str::random(30);

        return UploadedFile::fake()->image("{$randomName}.{$extension}", $width, $height);
    }
}
