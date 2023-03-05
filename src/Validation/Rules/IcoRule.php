<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;

class IcoRule implements RuleContract
{
    /**
     * Create a new rule instance.
     */
    public function __construct(protected bool $validateAres = true, protected ?int $cacheDuration = 86400)
    {
    }

    /**
     * @inheritDoc
     */
    public function passes(mixed $attribute, mixed $value): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        if (\preg_match('/^\\d{8}$/', $value) !== 1) {
            return false;
        }

        if (resolveApp()->runningUnitTests()) {
            return true;
        }

        if (! $this->validateChecksum($value)) {
            return false;
        }

        if (! $this->validateAres) {
            return true;
        }

        return $this->validateAres($value);
    }

    /**
     * @inheritDoc
     *
     * @return string|array<int, string>
     */
    public function message(): string|array
    {
        return mustTransString('validation.regex');
    }

    /**
     * Validate cheksum.
     */
    protected function validateChecksum(string $value): bool
    {
        $cheksum = 0;

        for ($i = 0; $i < 7; ++$i) {
            $cheksum += (int) $value[$i] * (8 - $i);
        }

        $cheksum = $cheksum % 11;

        if ($cheksum === 0) {
            $controll = 1;
        } elseif ($cheksum === 1) {
            $controll = 0;
        } else {
            $controll = 11 - $cheksum;
        }

        return (int) $value[7] === $controll;
    }

    /**
     * Validate using ares.
     */
    protected function validateAres(string $value): bool
    {
        $cache = resolveCacheManager()->getStore()->get(static::class.':'.$value);

        if (\is_bool($cache)) {
            return $cache;
        }

        $response = \file_get_contents("https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_std.cgi?ico={$value}");

        if (! \is_string($response)) {
            assertNever('ares not responding');
        }

        $passes = \str_contains($response, 'Shoda_ICO');

        if ($this->cacheDuration !== null) {
            resolveCacheManager()->put(static::class.':'.$value, $passes, $this->cacheDuration);
        }

        return $passes;
    }
}
