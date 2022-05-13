<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

class MediaValidity
{
    public const MIME_TYPES_IMAGE = [
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/svg+xml',
        'image/webp',
        'image/bmp',
    ];

    public const MIME_TYPES_VIDEO = [
        'video/mp4',
        'video/mpeg',
        'video/ogg',
        'video/quicktime',
        'video/webm',
    ];

    public const MAX_IMAGE = 10240;
    public const MAX_VIDEO = 10240;
    public const MAX_FILE = 10240;

    /**
     * Image validation rules.
     */
    public function image(): Validity
    {
        return Validity::make()->file()->mimetypes(static::MIME_TYPES_IMAGE)->max(static::MAX_IMAGE);
    }

    /**
     * Video validation rules.
     */
    public function video(): Validity
    {
        return Validity::make()->file()->mimetypes(static::MIME_TYPES_VIDEO)->max(static::MAX_VIDEO);
    }

    /**
     * Generic file validation rules.
     */
    public function file(): Validity
    {
        return Validity::make()->file()->max(static::MAX_FILE);
    }
}
