<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Tomchochola\Laratchi\Config\Config;

class IcoRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(protected bool $validateAres = true, protected int|null $cacheDuration = 86400) {}

    /**
     * @inheritDoc
     */
    public function validate(mixed $attribute, mixed $value, Closure $fail): void
    {
        if (!\is_string($value)) {
            $fail(\mustTransString('validation.regex'));

            return;
        }

        if (Config::inject()->appEnvIs(['testing'])) {
            return;
        }

        if (\preg_match('/^\\d{8}$/', $value) !== 1) {
            $fail(\mustTransString('validation.regex'));

            return;
        }

        if (!$this->validateChecksum($value)) {
            $fail(\mustTransString('validation.regex'));

            return;
        }

        if (!$this->validateAres) {
            return;
        }

        if ($this->validateAres($value)) {
            return;
        }

        $fail(\mustTransString('validation.regex'));
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

        return $controll === (int) $value[7];
    }

    /**
     * Validate using ares.
     */
    protected function validateAres(string $value): bool
    {
        $cache = \resolveCacheManager()
            ->getStore()
            ->get(static::class . ':' . $value);

        if (\is_bool($cache)) {
            return $cache;
        }

        $response = \file_get_contents("https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_std.cgi?ico={$value}");

        if (!\is_string($response)) {
            \assertNever('ares not responding');
        }

        $passes = \str_contains($response, 'Shoda_ICO');

        if ($this->cacheDuration !== null) {
            \resolveCacheManager()->put(static::class . ':' . $value, $passes, $this->cacheDuration);
        }

        return $passes;
    }
}
