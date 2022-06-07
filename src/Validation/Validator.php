<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as IlluminateValidator;

class Validator extends IlluminateValidator
{
    /**
     * @inheritDoc
     *
     * @param array<mixed> $data
     * @param array<mixed> $rules
     * @param array<mixed> $messages
     * @param array<mixed> $customAttributes
     */
    public function __construct(TranslatorContract $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);

        $this->dependentRules = \array_merge($this->dependentRules, [
            'ProhibitedWith',
            'ProhibitedWithAll',
            'ProhibitedWithout',
            'ProhibitedWithoutAll',
            'NullWith',
            'NullWithAll',
            'NullWithout',
            'NullWithoutAll',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validateBoolean(mixed $attribute, mixed $value): bool
    {
        return \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE) !== null;
    }

    /**
     * Validate that an attribute is prohibited when any other attribute exists.
     *
     * @param array<int, string> $parameters
     */
    public function validateProhibitedWith(string $attribute, mixed $value, array $parameters): bool
    {
        foreach ($parameters as $key) {
            if ($this->validateRequired($key, $this->getValue($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that an attribute is prohibited when any other attribute not exists.
     *
     * @param array<int, string> $parameters
     */
    public function validateProhibitedWithout(string $attribute, mixed $value, array $parameters): bool
    {
        foreach ($parameters as $key) {
            if (! $this->validateRequired($key, $this->getValue($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that an attribute is prohibited when all other attribute exists.
     *
     * @param array<int, string> $parameters
     */
    public function validateProhibitedWithAll(string $attribute, mixed $value, array $parameters): bool
    {
        foreach ($parameters as $key) {
            if (! $this->validateRequired($key, $this->getValue($key))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate that an attribute is prohibited when all other attribute not exists.
     *
     * @param array<int, string> $parameters
     */
    public function validateProhibitedWithoutAll(string $attribute, mixed $value, array $parameters): bool
    {
        foreach ($parameters as $key) {
            if ($this->validateRequired($key, $this->getValue($key))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate that an attribute is null.
     */
    public function validateNull(string $attribute, mixed $value): bool
    {
        return $value === null;
    }

    /**
     * Validate that an attribute is null when any other attribute exists.
     *
     * @param array<int, string> $parameters
     */
    public function validateNullWith(string $attribute, mixed $value, array $parameters): bool
    {
        if ($value === null) {
            return true;
        }

        foreach ($parameters as $key) {
            if ($this->validateRequired($key, $this->getValue($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that an attribute is null when any other attribute not exists.
     *
     * @param array<int, string> $parameters
     */
    public function validateNullWithout(string $attribute, mixed $value, array $parameters): bool
    {
        if ($value === null) {
            return true;
        }

        foreach ($parameters as $key) {
            if (! $this->validateRequired($key, $this->getValue($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that an attribute is null when all other attribute exists.
     *
     * @param array<int, string> $parameters
     */
    public function validateNullWithAll(string $attribute, mixed $value, array $parameters): bool
    {
        if ($value === null) {
            return true;
        }

        foreach ($parameters as $key) {
            if (! $this->validateRequired($key, $this->getValue($key))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate that an attribute is null when all other attribute not exists.
     *
     * @param array<int, string> $parameters
     */
    public function validateNullWithoutAll(string $attribute, mixed $value, array $parameters): bool
    {
        if ($value === null) {
            return true;
        }

        foreach ($parameters as $key) {
            if ($this->validateRequired($key, $this->getValue($key))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate the size of an attribute is less than a maximum value.
     *
     * @param array{int} $parameters
     */
    public function validateStrlenMax(string $attribute, mixed $value, array $parameters): bool
    {
        return \is_string($value) && \mb_strlen($value, 'ASCII') <= $parameters[0];
    }

    /**
     * Validate the size of an attribute is less than a maximum value.
     *
     * @param array{int} $parameters
     */
    public function validateStrlenMin(string $attribute, mixed $value, array $parameters): bool
    {
        return \is_string($value) && \mb_strlen($value, 'ASCII') >= $parameters[0];
    }

    /**
     * Validate the size of an attribute is less than a maximum value.
     *
     * @param array{int} $parameters
     */
    public function validateStrlen(string $attribute, mixed $value, array $parameters): bool
    {
        return \is_string($value) && \mb_strlen($value, 'ASCII') === $parameters[0];
    }

    /**
     * Replace all place-holders for the prohibited_with rule.
     *
     * @param array<int ,string> $parameters
     */
    protected function replaceProhibitedWith(string $message, string $attribute, string $rule, array $parameters): string
    {
        return \str_replace(':values', \implode(' / ', $this->getAttributeList($parameters)), $message);
    }

    /**
     * Replace all place-holders for the prohibited_with_all rule.
     *
     * @param array<int ,string> $parameters
     */
    protected function replaceProhibitedWithAll(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceProhibitedWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the prohibited_without rule.
     *
     * @param array<int ,string> $parameters
     */
    protected function replaceProhibitedWithout(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceProhibitedWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the prohibited_without_all rule.
     *
     * @param array<int ,string> $parameters
     */
    protected function replaceProhibitedWithoutAll(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceProhibitedWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the null_with rule.
     *
     * @param array<int ,string> $parameters
     */
    protected function replaceNullWith(string $message, string $attribute, string $rule, array $parameters): string
    {
        return \str_replace(':values', \implode(' / ', $this->getAttributeList($parameters)), $message);
    }

    /**
     * Replace all place-holders for the null_with_all rule.
     *
     * @param array<int ,string> $parameters
     */
    protected function replaceNullWithAll(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceProhibitedWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the null_without rule.
     *
     * @param array<int ,string> $parameters
     */
    protected function replaceNullWithout(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceProhibitedWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the null_without_all rule.
     *
     * @param array<int ,string> $parameters
     */
    protected function replaceNullWithoutAll(string $message, string $attribute, string $rule, array $parameters): string
    {
        return $this->replaceProhibitedWith($message, $attribute, $rule, $parameters);
    }

    /**
     * Replace all place-holders for the strlen_max rule.
     *
     * @param array{int} $parameters
     */
    protected function replaceStrlenMax(string $message, string $attribute, string $rule, array $parameters): string
    {
        return \str_replace(':max', (string) $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the strlen_min rule.
     *
     * @param array{int} $parameters
     */
    protected function replaceStrlenMin(string $message, string $attribute, string $rule, array $parameters): string
    {
        return \str_replace(':min', (string) $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the strlen rule.
     *
     * @param array{int} $parameters
     */
    protected function replaceStrlen(string $message, string $attribute, string $rule, array $parameters): string
    {
        return \str_replace(':size', (string) $parameters[0], $message);
    }

    /**
     * @inheritDoc
     */
    protected function getAttributeFromTranslations(mixed $name): string
    {
        $attributes = $this->translator->get('validation.attributes');

        if ($attributes === 'validation.attributes') {
            $attributes = [];
        }

        \assert(\is_array($attributes));

        $attribute = Arr::get($attributes, $name);

        if (\is_string($attribute)) {
            return $attribute;
        }

        $name = Str::afterLast($name, '.');

        $attribute = Arr::get($attributes, $name);

        if (\is_string($attribute)) {
            return $attribute;
        }

        return '';
    }
}
