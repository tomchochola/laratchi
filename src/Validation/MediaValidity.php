<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

class MediaValidity
{
    /**
     * Mime types for images.
     *
     * @var array<int, string>
     */
    public static array $mimeTypesImage = [
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/svg+xml',
        'image/svg',
        'image/webp',
        'image/bmp',
        'image/x-bmp',
        'image/x-ms-bmp',
        'image/heif',
        'image/heic',
    ];

    /**
     * Mime types for videos.
     *
     * @var array<int, string>
     */
    public static array $mimeTypesVideo = [
        'video/mp4',
        'video/mpeg',
        'video/ogg',
        'video/quicktime',
        'video/webm',
    ];

    /**
     * Mime types for files.
     *
     * @var array<int, string>
     */
    public static array $mimeTypesFile = [];

    public static int $maxImage = 10240;
    public static int $maxVideo = 10240;
    public static int $maxFile = 10240;

    /**
     * Image validation rules.
     */
    public function image(): Validity
    {
        $validity = Validity::make()->file()->max(static::$maxImage);

        if (\count(static::$mimeTypesImage) > 0) {
            $validity->mimetypes(static::$mimeTypesImage);
        }

        return $validity;
    }

    /**
     * Video validation rules.
     */
    public function video(): Validity
    {
        $validity = Validity::make()->file()->max(static::$maxVideo);

        if (\count(static::$mimeTypesVideo) > 0) {
            $validity->mimetypes(static::$mimeTypesVideo);
        }

        return $validity;
    }

    /**
     * Generic file validation rules.
     */
    public function file(): Validity
    {
        $validity = Validity::make()->file()->max(static::$maxFile);

        if (\count(static::$mimeTypesFile) > 0) {
            $validity->mimetypes(static::$mimeTypesFile);
        }

        return $validity;
    }
}
