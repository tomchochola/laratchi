<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Encoding;

class DataUri
{
    /**
     * Encode data to data uri.
     */
    public static function encode(string $mime, string $data): string
    {
        return 'data:' . $mime . ';base64,' . \base64_encode($data);
    }
}
