<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Encoding;

use Tomchochola\Laratchi\Exceptions\Panicker;
use Tomchochola\Laratchi\Support\Typer;

class Json
{
    /**
     * Encode data to json.
     *
     * @param array<mixed> $data
     */
    public static function encode(array $data): string
    {
        $json = \json_encode($data);

        if ($json === false) {
            Panicker::panic(__METHOD__, \json_last_error_msg(), \compact($data));
        }

        return $json;
    }

    /**
     * Decode json to array.
     *
     * @return array<mixed>
     */
    public static function decode(string $json): array
    {
        $data = \json_decode($json, true);

        if ($data === null) {
            Panicker::panic(__METHOD__, \json_last_error_msg(), \compact($json));
        }

        return Typer::assertArray($data);
    }
}
