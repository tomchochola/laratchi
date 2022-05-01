<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Translation;

use Illuminate\Support\Str;
use Illuminate\Translation\Translator;
use Tomchochola\Laratchi\Support\Facades\Facade;

class FakeTranslator extends Translator
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function swap(): void
    {
        $translator = resolveTranslator();

        $loader = $translator->getLoader();
        $locale = $translator->getLocale();
        $fallback = $translator->getFallback();

        $fake = new self($loader, $locale);

        $fake->setFallback($fallback);

        Facade::fake('translator', $fake);
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $replace
     *
     * @return string|array<mixed>
     */
    public function get(mixed $key, array $replace = [], mixed $locale = null, mixed $fallback = true): string|array
    {
        $value = parent::get($key, $replace, $locale, $fallback);

        if ($value === $key) {
            return Str::random();
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function has(mixed $key, mixed $locale = null, mixed $fallback = true): bool
    {
        return parent::get($key, [], $locale, $fallback) !== $key;
    }
}
