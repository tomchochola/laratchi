<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\ValidatedInput;

class Parser extends ValidatedInput
{
    use AssertTrait;
    use ParserTrait;
    use ParseTrait;

    /**
     * Mixed getter.
     */
    public function mixed(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->input;
        }

        return Arr::get($this->input, $key);
    }
}
