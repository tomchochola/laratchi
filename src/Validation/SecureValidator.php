<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\InvokableValidationRule;
use Illuminate\Validation\ValidationRuleParser;
use Tomchochola\Laratchi\Validation\Rules\CallbackRule;
use Tomchochola\Laratchi\Validation\Rules\CzBankAccountNumberRule;
use Tomchochola\Laratchi\Validation\Rules\IcoRule;
use Tomchochola\Laratchi\Validation\Rules\PostCodeRule;
use Tomchochola\Laratchi\Validation\Rules\RecaptchaRule;

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
     * Use attribtue instead of real translations.
     */
    public static bool $useRawAttributes = true;

    /**
     * Use computer errors.
     */
    public static bool $useComputerErrors = true;

    /**
     * Force prohibited.
     */
    public static bool $forceProhibited = true;

    /**
     * @var array<string, string|array<string, string>>
     */
    public static array $msgs = [
        'accepted' => 'accepted',
        'accepted_if' => 'accepted_if::other;:value',
        'active_url' => 'active_url',
        'after' => 'after::date',
        'after_or_equal' => 'after_or_equal::date',
        'alpha' => 'alpha',
        'alpha_dash' => 'alpha_dash',
        'alpha_num' => 'alpha_num',
        'array' => 'array',
        'ascii' => 'ascii',
        'before' => 'before::date',
        'before_or_equal' => 'before_or_equal::date',
        'between' => [
            'array' => 'between_array::min;:max',
            'file' => 'between_file::min;:max',
            'numeric' => 'between_numeric::min;:max',
            'string' => 'between_string::min;:max',
        ],
        'boolean' => 'boolean',
        'confirmed' => 'confirmed',
        'current_password' => 'current_password',
        'date' => 'date',
        'date_equals' => 'date_equals::date',
        'date_format' => 'date_format::format',
        'decimal' => 'decimal::decimal',
        'declined' => 'declined',
        'declined_if' => 'declined_if::other;:value',
        'different' => 'different::other',
        'digits' => 'digits::digits',
        'digits_between' => 'digits_between::min;:max',
        'dimensions' => 'dimensions',
        'distinct' => 'distinct',
        'doesnt_end_with' => 'doesnt_end_with::values',
        'doesnt_start_with' => 'doesnt_start_with::values',
        'email' => 'email',
        'ends_with' => 'ends_with::values',
        'enum' => 'enum',
        'exists' => 'exists',
        'file' => 'file',
        'filled' => 'filled',
        'gt' => [
            'array' => 'gt_array::value',
            'file' => 'gt_file::value',
            'numeric' => 'gt_numeric::value',
            'string' => 'gt_string::value',
        ],
        'gte' => [
            'array' => 'gte_array::value',
            'file' => 'gte_file::value',
            'numeric' => 'gte_numeric::value',
            'string' => 'gte_string::value',
        ],
        'image' => 'image',
        'in' => 'in',
        'in_array' => 'in_array::other',
        'integer' => 'integer',
        'ip' => 'ip',
        'ipv4' => 'ipv4',
        'ipv6' => 'ipv6',
        'json' => 'json',
        'lowercase' => 'lowercase',
        'lt' => [
            'array' => 'lt_array::value',
            'file' => 'lt_file::value',
            'numeric' => 'lt_numeric::value',
            'string' => 'lt_string::value',
        ],
        'lte' => [
            'array' => 'lte_array::value',
            'file' => 'lte_file::value',
            'numeric' => 'lte_numeric::value',
            'string' => 'lte_string::value',
        ],
        'mac_address' => 'mac_address',
        'max' => [
            'array' => 'max_array::max',
            'file' => 'max_file::max',
            'numeric' => 'max_numeric::max',
            'string' => 'max_string::max',
        ],
        'max_digits' => 'max_digits::max',
        'mimes' => 'mimes::values',
        'mimetypes' => 'mimetypes::values',
        'min' => [
            'array' => 'min_array::min',
            'file' => 'min_file::min',
            'numeric' => 'min_numeric::min',
            'string' => 'min_string::min',
        ],
        'min_digits' => 'min_digits::min',
        'multiple_of' => 'multiple_of::value',
        'not_in' => 'not_in',
        'not_regex' => 'not_regex',
        'numeric' => 'numeric',
        'password' => [
            'letters' => 'password_letters',
            'mixed' => 'password_mixed',
            'numbers' => 'password_numbers',
            'symbols' => 'password_symbols',
            'uncompromised' => 'password_uncompromised',
        ],
        'present' => 'present',
        'prohibited' => 'prohibited',
        'prohibited_if' => 'prohibited_if::other;:value',
        'prohibited_unless' => 'prohibited_unless::other;:values',
        'prohibits' => 'prohibits::other',
        'regex' => 'regex',
        'required' => 'required',
        'required_array_keys' => 'required_array_keys::values',
        'required_if' => 'required_if::other;:value',
        'required_if_accepted' => 'required_if_accepted::other',
        'required_unless' => 'required_unless::other;:values',
        'required_with' => 'required_with::values',
        'required_with_all' => 'required_with_all::values',
        'required_without' => 'required_without::values',
        'required_without_all' => 'required_without_all::values',
        'same' => 'same::other',
        'size' => [
            'array' => 'size_array::size',
            'file' => 'size_file::size',
            'numeric' => 'size_numeric::size',
            'string' => 'size_string::size',
        ],
        'starts_with' => 'starts_with::values',
        'string' => 'string',
        'timezone' => 'timezone',
        'unique' => 'unique',
        'uploaded' => 'uploaded',
        'uppercase' => 'uppercase',
        'url' => 'url',
        'ulid' => 'ulid',
        'uuid' => 'uuid',

        'prohibited_with' => 'prohibited_with::values',
        'prohibited_with_all' => 'prohibited_with_all::values',
        'prohibited_without' => 'prohibited_without::values',
        'prohibited_without_all' => 'prohibited_without_all::values',
        'null_with' => 'null_with::values',
        'null_with_all' => 'null_with_all::values',
        'null_without' => 'null_without::values',
        'null_without_all' => 'null_without_all::values',
        'strlen' => 'strlen::size',
        'strlen_max' => 'strlen_max::max',
        'strlen_min' => 'strlen_min::min',
        'null' => 'null',

        'throttled' => 'throttled::seconds',

        'auth.failed' => 'auth_failed',
        'auth.password' => 'auth_password',
        'auth.throttle' => 'auth_throttle::seconds',
        'auth.blocked' => 'auth_blocked',

        'passwords.reset' => 'passwords_reset',
        'passwords.sent' => 'passwords_sent',
        'passwords.throttled' => 'passwords_throttled',
        'passwords.token' => 'passwords_token',
        'passwords.user' => 'passwords_user',

        'passwords' => [
            'reset' => 'passwords_reset',
            'sent' => 'passwords_sent',
            'throttled' => 'passwords_throttled',
            'token' => 'passwords_token',
            'user' => 'passwords_user',
        ],

        'auth' => [
            'failed' => 'auth_failed',
            'password' => 'auth_password',
            'throttle' => 'auth_throttle::seconds',
            'blocked' => 'auth_blocked',
        ],

        CallbackRule::class => 'invalid',
        CzBankAccountNumberRule::class => 'invalid',
        IcoRule::class => 'invalid',
        PostCodeRule::class => 'invalid',
        RecaptchaRule::class => 'invalid',
    ];

    /**
     * @var array<string, string|array<string, string>>
     */
    public static array $customMsgs = [];

    /**
     * @inheritDoc
     */
    public function passes(): bool
    {
        \assert(static::$useComputerErrors === false || $this->allMessagesDefined());

        $passes = parent::passes();

        if (static::$forceProhibited === false || $passes === false) {
            return $passes;
        }

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

        return $this->messages->isEmpty();
    }

    /**
     * @inheritDoc
     */
    public function getDisplayableValue(mixed $attribute, mixed $value): string
    {
        if (static::$useComputerErrors === false) {
            return parent::getDisplayableValue($attribute, $value);
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'empty';
        }

        \assert(\is_scalar($value));

        return (string) $value;
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $parameters
     */
    public function makeReplacements(mixed $message, mixed $attribute, mixed $rule, mixed $parameters): string
    {
        $message = parent::makeReplacements($message, $attribute, $rule, $parameters);

        if (\count($parameters) > 0) {
            $message = \str_replace(\array_keys(Arr::prependKeysWith($parameters, ':')), \array_values($parameters), $message);
        }

        return $message;
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
        if (static::$useRawAttributes) {
            return $name;
        }

        if (static::$usePlaceholderAttributes) {
            return "{{ {$name} }}";
        }

        return parent::getAttributeFromTranslations($name);
    }

    /**
     * @inheritDoc
     */
    protected function getInlineMessage(mixed $attribute, mixed $rule): ?string
    {
        if (static::$useComputerErrors) {
            $snake = Str::snake($rule);

            if (\in_array($rule, $this->sizeRules, true)) {
                $type = $this->getAttributeType($attribute);

                $message = static::$customMsgs[$snake][$type] ?? static::$msgs[$snake][$type] ?? 'fallback';
            } else {
                $message = static::$customMsgs[$snake] ?? static::$msgs[$snake] ?? 'fallback';
            }

            \assert(\is_string($message));
            \assert($message !== 'fallback', "Json api validation message not defined for regular rule: [{$snake}]");

            return $message;
        }

        return parent::getInlineMessage($attribute, $rule);
    }

    /**
     * @inheritDoc
     *
     * @param ?array<mixed> $source
     */
    protected function getFromLocalArray(mixed $attribute, mixed $lowerRule, mixed $source = null): ?string
    {
        if (static::$useComputerErrors) {
            $message = static::$customMsgs[$lowerRule] ?? static::$msgs[$lowerRule] ?? 'fallback';

            \assert(\is_string($message));
            \assert($message !== 'fallback', "Json api validation message not defined for rule class: [{$lowerRule}]");

            return $message;
        }

        return parent::getFromLocalArray($attribute, $lowerRule);
    }

    /**
     * Check that all rules has defined message.
     */
    protected function allMessagesDefined(): bool
    {
        foreach ($this->rules as $attribute => $rules) {
            foreach ($rules as $rule) {
                [$rule] = ValidationRuleParser::parse($rule);

                if (blank($rule)) {
                    continue;
                }

                if (\in_array($rule, $this->excludeRules, true)) {
                    continue;
                }

                if ($rule instanceof RuleContract) {
                    if ($this->getFromLocalArray($attribute, $rule instanceof InvokableValidationRule ? \get_class($rule->invokable()) : $rule::class) === null) {
                        return false;
                    }
                } else {
                    if ($this->getInlineMessage($attribute, $rule) === null) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
