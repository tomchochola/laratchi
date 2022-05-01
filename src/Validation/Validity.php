<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Closure;
use Illuminate\Contracts\Support\Arrayable as ArrayableContract;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Dimensions;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\RequiredIf;
use Tomchochola\Laratchi\Rules\DelimitedRule;
use Tomchochola\Laratchi\Rules\ProhibitedIfRule;
use Tomchochola\Laratchi\Rules\RecaptchaRule;

/**
 * @implements ArrayableContract<int, mixed>
 */
class Validity implements ArrayableContract
{
    public const TINY_INT_MAX = 127;
    public const TINY_INT_MIN = -128;
    public const UNSIGNED_TINY_INT_MAX = 255;
    public const UNSIGNED_TINY_INT_MIN = 0;

    public const SMALL_INT_MAX = 32767;
    public const SMALL_INT_MIN = -32768;
    public const UNSIGNED_SMALL_INT_MAX = 65535;
    public const UNSIGNED_SMALL_INT_MIN = 0;

    public const MEDIUM_INT_MAX = 8388607;
    public const MEDIUM_INT_MIN = -8388608;
    public const UNSIGNED_MEDIUM_INT_MAX = 16777215;
    public const UNSIGNED_MEDIUM_INT_MIN = 0;

    public const INT_MAX = 2147483647;
    public const INT_MIN = -2147483648;
    public const UNSIGNED_INT_MAX = 4294967295;
    public const UNSIGNED_INT_MIN = 0;

    public const BIG_INT_MAX = \PHP_INT_MAX;
    public const BIG_INT_MIN = \PHP_INT_MIN;
    public const UNSIGNED_BIG_INT_MAX = '18446744073709551615';
    public const UNSIGNED_BIG_INT_MIN = 0;

    public const STRING_MAX = 255;
    public const TINY_TEXT_MAX = 256;
    public const TEXT_MAX = 65535;
    public const MEDIUM_TEXT_MAX = 16777215;
    public const LONG_TEXT_MAX = 4294967295;

    /**
     * Rule priorities.
     *
     * @var array<string, int>
     */
    public static array $priority = [
        'bail' => 1,
        'sometimes' => 2,
        'nullable' => 3,
        'required' => 4,
    ];

    /**
     * Rules.
     *
     * @var array<int, array<int, mixed>>
     */
    protected array $rules = [
        0 => [],
        1 => [],
        2 => [],
        3 => [],
        4 => [],
        5 => [],
        6 => [],
        7 => [],
        8 => [],
        9 => [],
        10 => [],
    ];

    /**
     * Create a new validity instance.
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Add new rule.
     *
     * @param ?array<int, mixed> $arguments
     *
     * @return $this
     */
    public function addRule(mixed $rule, ?array $arguments = null, ?int $level = null): static
    {
        if (\is_string($rule)) {
            $ruleName = Str::before($rule, ':');

            $level ??= static::$priority[$ruleName] ?? 5;
        }

        $this->rules[$level ?? 5][] = $arguments !== null ? [$rule, ...$arguments] : $rule;

        return $this;
    }

    /**
     * Add accepted rule.
     *
     * @return $this
     */
    public function accepted(): static
    {
        return $this->addRule('accepted');
    }

    /**
     * Add accepted_if rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function acceptedIf(string $field, array $values): static
    {
        return $this->addRule('accepted_if', [$field, ...$values]);
    }

    /**
     * Add active_url rule or url when testing.
     *
     * @return $this
     */
    public function activeUrl(): static
    {
        if (resolveApp()->runningUnitTests()) {
            return $this->addRule('url');
        }

        return $this->addRule('active_url');
    }

    /**
     * Add after rule.
     *
     * @return $this
     */
    public function after(string $dateOrField): static
    {
        return $this->addRule('after', [$dateOrField]);
    }

    /**
     * Add after_or_equal rule.
     *
     * @return $this
     */
    public function afterOrEqual(string $dateOrField): static
    {
        return $this->addRule('after_or_equal', [$dateOrField]);
    }

    /**
     * Add alpha rule.
     *
     * @return $this
     */
    public function alpha(): static
    {
        return $this->addRule('alpha');
    }

    /**
     * Add alpha_dash rule.
     *
     * @return $this
     */
    public function alphaDash(): static
    {
        return $this->addRule('alpha_dash');
    }

    /**
     * Add alpha_num rule.
     *
     * @return $this
     */
    public function alphaNum(): static
    {
        return $this->addRule('alpha_num');
    }

    /**
     * Add array rule.
     *
     * @param ?array<int, string> $keys
     *
     * @return $this
     */
    public function array(?array $keys = null): static
    {
        return $this->addRule('array', $keys);
    }

    /**
     * Add bail rule.
     *
     * @return $this
     */
    public function bail(): static
    {
        return $this->addRule('bail');
    }

    /**
     * Add before rule.
     *
     * @return $this
     */
    public function before(string $dateOrField): static
    {
        return $this->addRule('before', [$dateOrField]);
    }

    /**
     * Add before_or_equal rule.
     *
     * @return $this
     */
    public function beforeOrEqual(string $dateOrField): static
    {
        return $this->addRule('before_or_equal', [$dateOrField]);
    }

    /**
     * Add between rule.
     *
     * @param int|float|numeric-string $min
     * @param int|float|numeric-string $max
     *
     * @return $this
     */
    public function between(int|float|string $min, int|float|string $max): static
    {
        return $this->addRule('between', [$min, $max]);
    }

    /**
     * Add boolean rule.
     *
     * @return $this
     */
    public function boolean(): static
    {
        return $this->addRule('boolean');
    }

    /**
     * Add confirmed rule.
     *
     * @return $this
     */
    public function confirmed(): static
    {
        return $this->addRule('confirmed');
    }

    /**
     * Add current_password rule.
     *
     * @return $this
     */
    public function currentPassword(?string $guard = null): static
    {
        return $this->addRule('current_password', $guard !== null ? [$guard] : null);
    }

    /**
     * Add date rule.
     *
     * @return $this
     */
    public function date(): static
    {
        return $this->addRule('date');
    }

    /**
     * Add date_equals rule.
     *
     * @return $this
     */
    public function dateEquals(string $date): static
    {
        return $this->addRule('date_equals', [$date]);
    }

    /**
     * Add date_format rule.
     *
     * @return $this
     */
    public function dateFormat(string $dateFormat = 'Y-m-d'): static
    {
        return $this->addRule('date_format', [$dateFormat]);
    }

    /**
     * Add iso8601 rule.
     *
     * @return $this
     */
    public function iso8601(): static
    {
        return $this->dateFormat('Y-m-d\\TH:i:sP');
    }

    /**
     * Add declined rule.
     *
     * @return $this
     */
    public function declined(): static
    {
        return $this->addRule('declined');
    }

    /**
     * Add declined_if rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function declinedIf(string $field, array $values): static
    {
        return $this->addRule('declined_if', [$field, ...$values]);
    }

    /**
     * Add different rule.
     *
     * @return $this
     */
    public function different(string $field): static
    {
        return $this->addRule('different', [$field]);
    }

    /**
     * Add digits rule.
     *
     * @return $this
     */
    public function digits(int $length): static
    {
        return $this->addRule('digits', [$length]);
    }

    /**
     * Add digits_between rule.
     *
     * @return $this
     */
    public function digitsBetween(int $minLength, int $maxLength): static
    {
        return $this->addRule('digits_between', [$minLength, $maxLength]);
    }

    /**
     * Add dimensions rule.
     *
     * @return $this
     */
    public function dimensionsRule(?int $width = null, ?int $height = null, ?int $minWidth = null, ?int $maxWidth = null, ?int $minHeight = null, ?int $maxHeight = null, ?float $ratio = null): static
    {
        $dimensions = new Dimensions([]);

        if ($width !== null) {
            $dimensions->width($width);
        }

        if ($height !== null) {
            $dimensions->height($height);
        }

        if ($minWidth !== null) {
            $dimensions->minWidth($minWidth);
        }

        if ($maxWidth !== null) {
            $dimensions->maxWidth($maxWidth);
        }

        if ($minHeight !== null) {
            $dimensions->minHeight($minHeight);
        }

        if ($maxHeight !== null) {
            $dimensions->maxHeight($maxHeight);
        }

        if ($ratio !== null) {
            $dimensions->ratio($ratio);
        }

        return $this->addRule($dimensions);
    }

    /**
     * Add distinct rule.
     *
     * @return $this
     */
    public function distinct(bool $strict = false, bool $ignoreCase = false): static
    {
        $options = [];

        if ($strict) {
            $options[] = 'strict';
        }

        if ($ignoreCase) {
            $options[] = 'ignore_case';
        }

        return $this->addRule('distinct', $options);
    }

    /**
     * Add email rule.
     *
     * @return $this
     */
    public function email(bool $filter = true, bool $strict = true, bool $dns = true, bool $rfc = false, bool $spoof = false): static
    {
        if (resolveApp()->runningUnitTests()) {
            return $this->addRule('email');
        }

        $options = [];

        if ($filter) {
            $options[] = 'filter';
        }

        if ($strict) {
            $options[] = 'strict';
        }

        if ($dns) {
            $options[] = 'dns';
        }

        if ($rfc) {
            $options[] = 'rfc';
        }

        if ($spoof) {
            $options[] = 'spoof';
        }

        return $this->addRule('email', $options);
    }

    /**
     * Add ends_with rule.
     *
     * @param array<int, string> $ends
     *
     * @return $this
     */
    public function endsWith(array $ends): static
    {
        return $this->addRule('ends_with', $ends);
    }

    /**
     * Add enum rule.
     *
     * @param class-string $type
     *
     * @return $this
     */
    public function enumRule(string $type): static
    {
        return $this->addRule(new Enum($type));
    }

    /**
     * Add exclude rule.
     *
     * @return $this
     */
    public function exclude(): static
    {
        return $this->addRule('exclude');
    }

    /**
     * Add exclude_if rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function excludeIf(string $field, array $values): static
    {
        return $this->addRule('exclude_if', [$field, ...$values]);
    }

    /**
     * Add exclude_unless rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function excludeUnless(string $field, array $values): static
    {
        return $this->addRule('exclude_unless', [$field, ...$values]);
    }

    /**
     * Add exclude_without rule.
     *
     * @return $this
     */
    public function excludeWithout(string $field): static
    {
        return $this->addRule('exclude_without', [$field]);
    }

    /**
     * Add exists rule.
     *
     * @param array<int, string> $wheres
     *
     * @return $this
     */
    public function exists(string $table, string $column, array $wheres): static
    {
        return $this->addRule('exists', [$table, $column, ...$wheres]);
    }

    /**
     * Add file rule.
     *
     * @return $this
     */
    public function file(): static
    {
        return $this->addRule('file');
    }

    /**
     * Add filled rule.
     *
     * @return $this
     */
    public function filled(): static
    {
        return $this->addRule('filled');
    }

    /**
     * Add gt rule.
     *
     * @return $this
     */
    public function gt(string $field): static
    {
        return $this->addRule('gt', [$field]);
    }

    /**
     * Add gte rule.
     *
     * @return $this
     */
    public function gte(string $field): static
    {
        return $this->addRule('gte', [$field]);
    }

    /**
     * Add image rule.
     *
     * @return $this
     */
    public function image(): static
    {
        return $this->addRule('image');
    }

    /**
     * Add in rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function in(array $values): static
    {
        return $this->addRule('in', $values);
    }

    /**
     * Add in_array rule.
     *
     * @return $this
     */
    public function inArray(string $field): static
    {
        return $this->addRule('in_array', [$field]);
    }

    /**
     * Add integer rule.
     *
     * @return $this
     */
    public function integer(): static
    {
        return $this->addRule('integer');
    }

    /**
     * Add ip rule.
     *
     * @return $this
     */
    public function ip(): static
    {
        return $this->addRule('ip');
    }

    /**
     * Add ipv4 rule.
     *
     * @return $this
     */
    public function ipv4(): static
    {
        return $this->addRule('ipv4');
    }

    /**
     * Add ipv6 rule.
     *
     * @return $this
     */
    public function ipv6(): static
    {
        return $this->addRule('ipv6');
    }

    /**
     * Add mac_address rule.
     *
     * @return $this
     */
    public function macAddress(): static
    {
        return $this->addRule('mac_address');
    }

    /**
     * Add json rule.
     *
     * @return $this
     */
    public function json(): static
    {
        return $this->addRule('json');
    }

    /**
     * Add lt rule.
     *
     * @return $this
     */
    public function lt(string $field): static
    {
        return $this->addRule('lt', [$field]);
    }

    /**
     * Add lte rule.
     *
     * @return $this
     */
    public function lte(string $field): static
    {
        return $this->addRule('lte', [$field]);
    }

    /**
     * Add max rule.
     *
     * @param int|float|numeric-string $max
     *
     * @return $this
     */
    public function max(int|float|string $max): static
    {
        return $this->addRule('max', [$max]);
    }

    /**
     * Add mimetypes rule.
     *
     * @param array<int, string> $mimeTypes
     *
     * @return $this
     */
    public function mimetypes(array $mimeTypes): static
    {
        return $this->addRule('mimetypes', $mimeTypes);
    }

    /**
     * Add mimes rule.
     *
     * @param array<int, string> $extensions
     *
     * @return $this
     */
    public function mimes(array $extensions): static
    {
        return $this->addRule('mimes', $extensions);
    }

    /**
     * Add min rule.
     *
     * @param int|float|numeric-string $min
     *
     * @return $this
     */
    public function min(int|float|string $min): static
    {
        return $this->addRule('min', [$min]);
    }

    /**
     * Add multiple_of rule.
     *
     * @param int|float|numeric-string $multipleOf
     *
     * @return $this
     */
    public function multipleOf(int|float|string $multipleOf): static
    {
        return $this->addRule('multiple_of', [$multipleOf]);
    }

    /**
     * Add not_if rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function notInRule(array $values): static
    {
        return $this->addRule('not_if', $values);
    }

    /**
     * Add not_regex rule.
     *
     * @return $this
     */
    public function notRegex(string $pattern): static
    {
        return $this->addRule('not_regex', [$pattern]);
    }

    /**
     * Add nullable rule.
     *
     * @return $this
     */
    public function nullable(): static
    {
        return $this->addRule('nullable');
    }

    /**
     * Add numeric rule.
     *
     * @return $this
     */
    public function numeric(): static
    {
        return $this->addRule('numeric');
    }

    /**
     * Add password rule.
     *
     * @return $this
     */
    public function passwordRule(int $min): static
    {
        return $this->addRule(new Password($min));
    }

    /**
     * Add default password rule.
     *
     * @return $this
     */
    public function defaultPassword(): static
    {
        return $this->addRule(Password::default());
    }

    /**
     * Add present rule.
     *
     * @return $this
     */
    public function present(): static
    {
        return $this->addRule('present');
    }

    /**
     * Add prohibited rule.
     *
     * @return $this
     */
    public function prohibited(): static
    {
        return $this->addRule('prohibited');
    }

    /**
     * Add prohibited_if rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function prohibitedIf(string $field, array $values): static
    {
        return $this->addRule('prohibited_if', [$field, ...$values]);
    }

    /**
     * Add prohibited_unless rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function prohibitedUnless(string $field, array $values): static
    {
        return $this->addRule('prohibited_unless', [$field, ...$values]);
    }

    /**
     * Add prohibits rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function prohibits(array $fields): static
    {
        return $this->addRule('prohibits', $fields);
    }

    /**
     * Add regex rule.
     *
     * @return $this
     */
    public function regex(string $pattern): static
    {
        return $this->addRule('regex', [$pattern]);
    }

    /**
     * Add required rule.
     *
     * @return $this
     */
    public function required(): static
    {
        return $this->addRule('required');
    }

    /**
     * Add required_if rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function requiredIf(string $field, array $values): static
    {
        return $this->addRule('required_if', [$field, ...$values]);
    }

    /**
     * Add required_unless rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function requiredUnless(string $field, array $values): static
    {
        return $this->addRule('required_unless', [$field, $values]);
    }

    /**
     * Add required_with rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function requiredWith(array $fields): static
    {
        return $this->addRule('required_with', $fields);
    }

    /**
     * Add required_with_all rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function requiredWithAll(array $fields): static
    {
        return $this->addRule('required_with_all', $fields);
    }

    /**
     * Add required_without rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function requiredWithout(array $fields): static
    {
        return $this->addRule('required_without', $fields);
    }

    /**
     * Add required_without_all rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function requiredWithoutAll(array $fields): static
    {
        return $this->addRule('required_without_all', $fields);
    }

    /**
     * Add required_array_keys rule.
     *
     * @param array<int, string> $keys
     *
     * @return $this
     */
    public function requiredArrayKeys(array $keys): static
    {
        return $this->addRule('required_array_keys', $keys);
    }

    /**
     * Add same rule.
     *
     * @return $this
     */
    public function same(string $field): static
    {
        return $this->addRule('same', [$field]);
    }

    /**
     * Add size rule.
     *
     * @param int|float|numeric-string $size
     *
     * @return $this
     */
    public function size(int|float|string $size): static
    {
        return $this->addRule('size', [$size]);
    }

    /**
     * Add starts_with rule.
     *
     * @param array<int, string> $startsWith
     *
     * @return $this
     */
    public function startsWith(array $startsWith): static
    {
        return $this->addRule('starts_with', $startsWith);
    }

    /**
     * Add string rule.
     *
     * @return $this
     */
    public function string(): static
    {
        return $this->addRule('string');
    }

    /**
     * Add timezone rule.
     *
     * @return $this
     */
    public function timezone(): static
    {
        return $this->addRule('timezone');
    }

    /**
     * Add unique rule.
     *
     * @param array<int, string> $wheres
     *
     * @return $this
     */
    public function unique(string $table, string $column, array $wheres): static
    {
        return $this->addRule('unique', [$table, $column, ...$wheres]);
    }

    /**
     * Add url rule.
     *
     * @return $this
     */
    public function url(): static
    {
        return $this->addRule('url');
    }

    /**
     * Add uuid rule.
     *
     * @return $this
     */
    public function uuid(): static
    {
        return $this->addRule('uuid');
    }

    /**
     * Add closure rule.
     *
     * @return $this
     */
    public function closure(Closure $closure): static
    {
        return $this->addRule($closure);
    }

    /**
     * Add required_if rule.
     *
     * @return $this
     */
    public function requiredIfRule(bool|callable $condition): static
    {
        return $this->addRule(new RequiredIf($condition));
    }

    /**
     * Add sometimes rule.
     *
     * @return $this
     */
    public function sometimes(): static
    {
        return $this->addRule('sometimes');
    }

    /**
     * Add prohibited_with rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function prohibitedWith(array $fields): static
    {
        return $this->addRule('prohibited_with', $fields);
    }

    /**
     * Add prohibited_without rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function prohibitedWithout(array $fields): static
    {
        return $this->addRule('prohibited_without', $fields);
    }

    /**
     * Add prohibited_with_all rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function prohibitedWithAll(array $fields): static
    {
        return $this->addRule('prohibited_with_all', $fields);
    }

    /**
     * Add prohibited_without_all rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function prohibitedWithoutAll(array $fields): static
    {
        return $this->addRule('prohibited_without_all', $fields);
    }

    /**
     * Add null_with rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function nullWith(array $fields): static
    {
        return $this->addRule('null_with', $fields);
    }

    /**
     * Add null_without rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function nullWithout(array $fields): static
    {
        return $this->addRule('null_without', $fields);
    }

    /**
     * Add null_with_all rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function nullWithAll(array $fields): static
    {
        return $this->addRule('null_with_all', $fields);
    }

    /**
     * Add null_without_all rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function nullWithoutAll(array $fields): static
    {
        return $this->addRule('null_without_all', $fields);
    }

    /**
     * Add strlen rule.
     *
     * @return $this
     */
    public function strlen(int $length): static
    {
        return $this->addRule('strlen', [$length]);
    }

    /**
     * Add strlen_max rule.
     *
     * @return $this
     */
    public function strlenMax(int $max): static
    {
        return $this->addRule('strlen_max', [$max]);
    }

    /**
     * Add strlen_min rule.
     *
     * @return $this
     */
    public function strlenMin(int $max): static
    {
        return $this->addRule('strlen_min', [$max]);
    }

    /**
     * Add delimited rule.
     *
     * @param string|array<string, mixed>|RuleContract $rules
     * @param array<string, string> $customMessages
     *
     * @return $this
     */
    public function delimitedRule(string|array|RuleContract $rules, array $customMessages = []): static
    {
        return $this->addRule(new DelimitedRule($rules, $customMessages));
    }

    /**
     * Add prohibited if rule.
     *
     * @return $this
     */
    public function prohibitedIfRule(bool|callable $condition): static
    {
        return $this->addRule(new ProhibitedIfRule($condition));
    }

    /**
     * Add recaptcha rule.
     *
     * @return $this
     */
    public function recaptchaRule(): static
    {
        if (resolveApp()->runningUnitTests()) {
            return $this;
        }

        $secret = mustConfigString('services.recaptcha.secret');
        $message = mustTransString('validation.captcha');

        return $this->addRule(new RecaptchaRule($secret, $message));
    }

    /**
     * Add default string rules.
     *
     * @return $this
     */
    public function defaultString(?int $max = null): static
    {
        $max ??= static::STRING_MAX;

        \assert($max <= static::STRING_MAX);

        return $this->string()->max($max);
    }

    /**
     * Add tiny text rules.
     *
     * @return $this
     */
    public function tinyText(?int $max = null): static
    {
        $max ??= static::TINY_TEXT_MAX;

        \assert($max <= static::TINY_TEXT_MAX);

        return $this->string()->strlenMax($max);
    }

    /**
     * Add text rules.
     *
     * @return $this
     */
    public function text(?int $max = null): static
    {
        $max ??= static::TEXT_MAX;

        \assert($max <= static::TEXT_MAX);

        return $this->string()->strlenMax($max);
    }

    /**
     * Add medium text rules.
     *
     * @return $this
     */
    public function mediumText(?int $max = null): static
    {
        $max ??= static::MEDIUM_TEXT_MAX;

        \assert($max <= static::MEDIUM_TEXT_MAX);

        return $this->string()->strlenMax($max);
    }

    /**
     * Add long text rules.
     *
     * @return $this
     */
    public function longText(?int $max = null): static
    {
        $max ??= static::LONG_TEXT_MAX;

        \assert($max <= static::LONG_TEXT_MAX);

        return $this->string()->strlenMax($max);
    }

    /**
     * Add tiny int rules.
     *
     * @return $this
     */
    public function tinyInt(?int $min = null, ?int $max = null): static
    {
        $min ??= static::TINY_INT_MIN;

        \assert($min >= static::TINY_INT_MIN);

        $max ??= static::TINY_INT_MAX;

        \assert($max <= static::TINY_INT_MAX);

        return $this->integer()->min($min)->max($max);
    }

    /**
     * Add unsigned tiny int rules.
     *
     * @return $this
     */
    public function unsignedTinyInt(?int $min = null, ?int $max = null): static
    {
        $min ??= static::UNSIGNED_TINY_INT_MIN;

        \assert($min >= static::UNSIGNED_TINY_INT_MIN);

        $max ??= static::UNSIGNED_TINY_INT_MAX;

        \assert($max <= static::UNSIGNED_TINY_INT_MAX);

        return $this->integer()->min($min)->max($max);
    }

    /**
     * Add small int rules.
     *
     * @return $this
     */
    public function smallInt(?int $min = null, ?int $max = null): static
    {
        $min ??= static::SMALL_INT_MIN;

        \assert($min >= static::SMALL_INT_MIN);

        $max ??= static::SMALL_INT_MAX;

        \assert($max <= static::SMALL_INT_MAX);

        return $this->integer()->min($min)->max($max);
    }

    /**
     * Add unsigned small int rules.
     *
     * @return $this
     */
    public function unsignedSmallInt(?int $min = null, ?int $max = null): static
    {
        $min ??= static::UNSIGNED_SMALL_INT_MIN;

        \assert($min >= static::UNSIGNED_SMALL_INT_MIN);

        $max ??= static::UNSIGNED_SMALL_INT_MAX;

        \assert($max <= static::UNSIGNED_SMALL_INT_MAX);

        return $this->integer()->min($min)->max($max);
    }

    /**
     * Add medium int rules.
     *
     * @return $this
     */
    public function mediumInt(?int $min = null, ?int $max = null): static
    {
        $min ??= static::MEDIUM_INT_MIN;

        \assert($min >= static::MEDIUM_INT_MIN);

        $max ??= static::MEDIUM_INT_MAX;

        \assert($max <= static::MEDIUM_INT_MAX);

        return $this->integer()->min($min)->max($max);
    }

    /**
     * Add unsigned medium int rules.
     *
     * @return $this
     */
    public function unsignedMediumInt(?int $min = null, ?int $max = null): static
    {
        $min ??= static::UNSIGNED_MEDIUM_INT_MIN;

        \assert($min >= static::UNSIGNED_MEDIUM_INT_MIN);

        $max ??= static::UNSIGNED_MEDIUM_INT_MAX;

        \assert($max <= static::UNSIGNED_MEDIUM_INT_MAX);

        return $this->integer()->min($min)->max($max);
    }

    /**
     * Add int rules.
     *
     * @return $this
     */
    public function int(?int $min = null, ?int $max = null): static
    {
        $min ??= static::INT_MIN;

        \assert($min >= static::INT_MIN);

        $max ??= static::INT_MAX;

        \assert($max <= static::INT_MAX);

        return $this->integer()->min($min)->max($max);
    }

    /**
     * Add unsigned int rules.
     *
     * @return $this
     */
    public function unsignedInt(?int $min = null, ?int $max = null): static
    {
        $min ??= static::UNSIGNED_INT_MIN;

        \assert($min >= static::UNSIGNED_INT_MIN);

        $max ??= static::UNSIGNED_INT_MAX;

        \assert($max <= static::UNSIGNED_INT_MAX);

        return $this->integer()->min($min)->max($max);
    }

    /**
     * Add big int rules.
     *
     * @return $this
     */
    public function bigInt(?int $min = null, ?int $max = null): static
    {
        $min ??= static::BIG_INT_MIN;

        \assert($min >= static::BIG_INT_MIN);

        $max ??= static::BIG_INT_MAX;

        \assert($max <= static::BIG_INT_MAX);

        return $this->integer()->min($min)->max($max);
    }

    /**
     * Add unsigned big int rules.
     *
     * @param int|numeric-string|null $max
     *
     * @return $this
     */
    public function unsignedBigInt(?int $min = null, int|string $max = null): static
    {
        $min ??= static::UNSIGNED_BIG_INT_MIN;

        \assert($min >= static::UNSIGNED_BIG_INT_MIN);

        $max ??= static::UNSIGNED_BIG_INT_MAX;

        \assert($max <= static::UNSIGNED_BIG_INT_MAX);

        return $this->integer()->min($min)->max($max);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return Arr::flatten($this->rules, 1);
    }
}
