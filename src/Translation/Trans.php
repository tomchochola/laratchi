<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Translation;

use Illuminate\Translation\Translator;
use Tomchochola\Laratchi\Container\InjectTrait;
use Tomchochola\Laratchi\Exceptions\Panicker;
use Tomchochola\Laratchi\Support\Resolver;

class Trans
{
    use InjectTrait;

    /**
     * Translator.
     */
    public Translator $translator;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translator = Resolver::resolveTranslator();
    }

    /**
     * Must translate to string.
     *
     * @param array<string, string> $replace
     */
    public function assertString(string $key, array $replace = [], string|null $locale = null, bool $fallback = true): string
    {
        \assert($this->translator->has($key, $locale, $fallback), Panicker::message(__METHOD__, 'translation must exists', \compact('key', 'locale', 'fallback')));

        $value = $this->translator->get($key, $replace, $locale, $fallback);

        \assert(\is_string($value), Panicker::message(__METHOD__, 'assertion failed', \compact('key', 'value', 'locale', 'fallback')));

        return $value;
    }

    /**
     * Must translate to array.
     *
     * @param array<string, string> $replace
     *
     * @return array<string>
     */
    public function assertArray(string $key, array $replace = [], string|null $locale = null, bool $fallback = true): array
    {
        \assert($this->translator->has($key, $locale, $fallback), Panicker::message(__METHOD__, 'translation must exists', \compact('key', 'locale', 'fallback')));

        $value = $this->translator->get($key, $replace, $locale, $fallback);

        \assert(\is_array($value), Panicker::message(__METHOD__, 'assertion failed', \compact('key', 'value', 'locale', 'fallback')));

        return $value;
    }
}
