<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Translation;

use Illuminate\Translation\Translator;
use Tomchochola\Laratchi\Container\InjectTrait;
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
    public function assertString(string $key, array $replace = [], ?string $locale = null, bool $fallback = false): string
    {
        \assert($this->translator->has($key, $locale, $fallback), "key:[{$key}] translation must exist");

        $value = $this->translator->get($key, $replace, $locale, $fallback);

        \assert(\is_string($value), \sprintf("key:[{$key}] value:[%s] translation is not string", \get_debug_type($value)));

        return $value;
    }

    /**
     * Must translate to array.
     *
     * @param array<string, string> $replace
     *
     * @return array<string>
     */
    public function assertArray(string $key, array $replace = [], ?string $locale = null, bool $fallback = false): array
    {
        \assert($this->translator->has($key, $locale, $fallback), "key:[{$key}] translation must exist");

        $value = $this->translator->get($key, $replace, $locale, $fallback);

        \assert(\is_array($value), \sprintf("key:[{$key}] value:[%s] translation is not array", \get_debug_type($value)));

        return $value;
    }
}
