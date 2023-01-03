<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SecureValidator extends Validator
{
    /**
     * Excluded keys.
     *
     * @var array<int, string>
     */
    public static array $excluded = [];

    /**
     * Bail on every attribute.
     */
    public static bool $bail = true;

    /**
     * Use {{ attribute }} instead of real translations.
     */
    public static bool $usePlaceholderAttributes = true;

    /**
     * @inheritDoc
     */
    public function passes(): bool
    {
        $passes = parent::passes();

        $extraAttributes = \array_diff_key(
            Arr::dot($this->data),
            $this->rules,
        );

        foreach ($extraAttributes as $attribute => $value) {
            if (\count($this->getExplicitKeys($attribute)) === 0) {
                if (\in_array($attribute, static::$excluded, true)) {
                    continue;
                }

                if (Str::endsWith($attribute, '_confirmation')) {
                    $attr = Str::before($attribute, '_confirmation');

                    if (\in_array('confirmed', $this->rules[$attr] ?? [], true)) {
                        continue;
                    }
                }

                $this->addFailure($attribute, 'prohibited', []);
            }
        }

        return $this->messages->isEmpty() && $passes;
    }

    /**
     * @inheritDoc
     */
    protected function shouldStopValidating(mixed $attribute): bool
    {
        if (static::$bail) {
            return $this->messages->has($this->replacePlaceholderInString($attribute));
        }

        return parent::shouldStopValidating($attribute);
    }

    /**
     * @inheritDoc
     */
    protected function getAttributeFromTranslations(mixed $name): string
    {
        if (static::$usePlaceholderAttributes) {
            return "{{ {$name} }}";
        }

        return $name;
    }
}
