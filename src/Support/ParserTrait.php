<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

trait ParserTrait
{
    /**
     * Parser getter.
     */
    public function parser(?string $key = null): Parser
    {
        $value = $this->mixed($key);

        if (\is_array($value)) {
            return new Parser($value);
        }

        return new Parser(['scalar' => $value]);
    }

    /**
     * Parsers getter.
     *
     * @return array<int, Parser>
     */
    public function parsers(?string $key = null): array
    {
        $parsers = [];

        $values = $this->mixed($key);

        if (! \is_array($values)) {
            return [new Parser(['scalar' => $values])];
        }

        foreach ($values as $value) {
            if (\is_array($value)) {
                $parsers[] = new Parser($value);
            } else {
                $parsers[] = new Parser(['scalar' => $value]);
            }
        }

        return $parsers;
    }
}
