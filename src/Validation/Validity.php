<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Closure;
use Illuminate\Contracts\Support\Arrayable as ArrayableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Builder as SchmeaBuilder;
use Illuminate\Validation\Rules\Dimensions;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\ExcludeIf;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\NotIn;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\ProhibitedIf;
use Illuminate\Validation\Rules\RequiredIf;
use Tomchochola\Laratchi\Validation\Rules\CallbackRule;

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
    public const UNSIGNED_BIG_INT_MAX = \PHP_INT_MAX;
    public const UNSIGNED_BIG_INT_MIN = 0;

    public const TINY_TEXT_MAX = 256;
    public const TEXT_MAX = 65535;
    public const MEDIUM_TEXT_MAX = 16777215;
    public const LONG_TEXT_MAX = 4294967295;

    public const VARCHAR_MAX = 65535;

    /**
     * Bail flag.
     */
    public bool $bail = false;

    /**
     * Sometimes flag.
     */
    public bool $sometimes = false;

    /**
     * Nullable flag.
     */
    public bool $nullable = false;

    /**
     * Required flag.
     */
    public bool $required = false;

    /**
     * Filled flag.
     */
    public bool $filled = false;

    /**
     * Rules.
     *
     * @var array<int, mixed>
     */
    protected array $rules = [];

    /**
     * Skip next addRule class.
     */
    protected bool $skipNext = false;

    /**
     * Validity constructor.
     */
    final public function __construct()
    {
    }

    /**
     * Create a new validity instance.
     */
    public static function make(): static
    {
        return new static();
    }

    /**
     * Add new rule.
     *
     * @param ?array<int, mixed> $arguments
     *
     * @return $this
     */
    public function addRule(mixed $rule, ?array $arguments = null): static
    {
        \assert($this->skipNext === false);

        if (\is_string($rule)) {
            if ($arguments !== null && \count($arguments) > 0) {
                $rule = $rule.(\str_contains($rule, ':') ? ',' : ':').$this->formatArguments($arguments);
            }
        }

        if (! \in_array($rule, $this->rules, true)) {
            $this->rules[] = $rule;
        }

        return $this;
    }

    /**
     * Conditionally add rule.
     *
     * @param Closure(static): void $closure
     *
     * @return $this
     */
    public function when(bool $condition, Closure $closure): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        if ($condition) {
            $closure($this);
        }

        return $this;
    }

    /**
     * Skip next rule if flag not true.
     *
     * @return $this
     */
    public function if(bool $flag): static
    {
        $this->skipNext = $flag === false;

        return $this;
    }

    /**
     * Skip next rule if flag true.
     *
     * @return $this
     */
    public function ifNot(bool $flag): static
    {
        $this->skipNext = $flag;

        return $this;
    }

    /**
     * Call the given Closure with this instance then return the instance.
     *
     * @param Closure(static): void $callback
     *
     * @return $this
     */
    public function tap(Closure $callback): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $callback($this);

        return $this;
    }

    /**
     * Add accepted rule.
     *
     * @return $this
     */
    public function accepted(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('accepted_if', [$field, ...$values]);
    }

    /**
     * Add active_url rule or url when testing.
     *
     * @return $this
     */
    public function activeUrl(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('after', [$dateOrField]);
    }

    /**
     * Add after_or_equal rule.
     *
     * @return $this
     */
    public function afterOrEqual(string $dateOrField): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('after_or_equal', [$dateOrField]);
    }

    /**
     * Add alpha rule.
     *
     * @return $this
     */
    public function alpha(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('alpha');
    }

    /**
     * Add alpha_dash rule.
     *
     * @return $this
     */
    public function alphaDash(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('alpha_dash');
    }

    /**
     * Add alpha_num rule.
     *
     * @return $this
     */
    public function alphaNum(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('alpha_num');
    }

    /**
     * Add collection rule.
     *
     * @return $this
     */
    public function collection(int $maxItems, ?int $minItems = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        \assert($maxItems >= 0);

        $rule = $this->addRule('array')->max($maxItems);

        if ($minItems !== null) {
            \assert($minItems <= $maxItems);
            \assert($minItems >= 0);

            $rule->min($minItems);
        }

        return $rule;
    }

    /**
     * Add array rule.
     *
     * @param ?array<int, string> $structure
     * @param ?array<int, string> $keys
     *
     * @return $this
     */
    public function array(?array $structure = null, ?array $keys = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $rule = $this->addRule('array', $structure);

        if ($keys !== null) {
            $rule->requiredArrayKeys($keys);
        }

        return $rule;
    }

    /**
     * Add ascii rule.
     *
     * @return $this
     */
    public function ascii(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('ascii');
    }

    /**
     * Add object rule.
     *
     * @param ?array<int, string> $keys
     * @param ?array<int, string> $structure
     *
     * @return $this
     */
    public function object(?array $keys = null, ?array $structure = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $rule = $this->addRule('array', $structure);

        if ($keys !== null) {
            $rule->requiredArrayKeys($keys);
        }

        return $rule;
    }

    /**
     * Add bail rule.
     *
     * @return $this
     */
    public function bail(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $this->bail = true;

        return $this;
    }

    /**
     * Add before rule.
     *
     * @return $this
     */
    public function before(string $dateOrField): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('before', [$dateOrField]);
    }

    /**
     * Add before_or_equal rule.
     *
     * @return $this
     */
    public function beforeOrEqual(string $dateOrField): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('before_or_equal', [$dateOrField]);
    }

    /**
     * Add between rule.
     *
     * @return $this
     */
    public function between(int|float $min, int|float $max): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        \assert($max >= $min);

        return $this->addRule('between', [$min, $max]);
    }

    /**
     * Add boolean rule.
     *
     * @return $this
     */
    public function boolean(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('boolean');
    }

    /**
     * Add true rule.
     *
     * @return $this
     */
    public function true(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->boolean()->in(['1', 1, true]);
    }

    /**
     * Add false rule.
     *
     * @return $this
     */
    public function false(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->boolean()->in(['0', 0, false]);
    }

    /**
     * Add confirmed rule.
     *
     * @return $this
     */
    public function confirmed(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('confirmed');
    }

    /**
     * Add current_password rule.
     *
     * @return $this
     */
    public function currentPassword(?string $guard = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('current_password', $guard !== null ? [$guard] : null);
    }

    /**
     * Add date rule.
     *
     * @return $this
     */
    public function date(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('date');
    }

    /**
     * Add date_equals rule.
     *
     * @return $this
     */
    public function dateEquals(string $date): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('date_equals', [$date]);
    }

    /**
     * Add date_format rule.
     *
     * @return $this
     */
    public function dateFormat(string $dateFormat = 'Y-m-d'): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('date_format', [$dateFormat]);
    }

    /**
     * Add decimal rule.
     *
     * @return $this
     */
    public function decimal(int $min, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        \assert($max === null || $max >= $min);

        return $this->addRule('between', $max === null ? [$min] : [$min, $max]);
    }

    /**
     * Add declined rule.
     *
     * @return $this
     */
    public function declined(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('declined_if', [$field, ...$values]);
    }

    /**
     * Add different rule.
     *
     * @return $this
     */
    public function different(string $field): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('different', [$field]);
    }

    /**
     * Add digits rule.
     *
     * @return $this
     */
    public function digits(int $length): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('digits', [$length]);
    }

    /**
     * Add max_digits rule.
     *
     * @return $this
     */
    public function maxDigits(int $max): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('max_digits', [$max]);
    }

    /**
     * Add min_digits rule.
     *
     * @return $this
     */
    public function minDigits(int $min): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('min_digits', [$min]);
    }

    /**
     * Add digits_between rule.
     *
     * @return $this
     */
    public function digitsBetween(int $minLength, int $maxLength): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        \assert($maxLength >= $minLength);

        return $this->addRule('digits_between', [$minLength, $maxLength]);
    }

    /**
     * Add dimensions rule.
     *
     * @return $this
     */
    public function dimensionsRule(?int $width = null, ?int $height = null, ?int $minWidth = null, ?int $maxWidth = null, ?int $minHeight = null, ?int $maxHeight = null, ?float $ratio = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
     * Add strict ignore case distinct rule.
     *
     * @return $this
     */
    public function strictDistinct(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('distinct', ['strict', 'ignore_case']);
    }

    /**
     * Add doesnt_start_with rule.
     *
     * @param array<int, string> $ends
     *
     * @return $this
     */
    public function doesntStartWith(array $ends): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('doesnt_start_with', $ends);
    }

    /**
     * Add doesnt_end_with rule.
     *
     * @param array<int, string> $ends
     *
     * @return $this
     */
    public function doesntEndWith(array $ends): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('doesnt_end_with', $ends);
    }

    /**
     * Add email rule.
     *
     * @return $this
     */
    public function email(bool $filterUnicode = true, bool $strict = true, bool $dns = true, bool $rfc = false, bool $spoof = true, bool $filter = false): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        \assert(! $filterUnicode || ! $filter, 'filter and filter_unicode can not coexist');
        \assert(! $strict || ! $rfc, 'strict and rfc can not coexist');

        if (resolveApp()->runningUnitTests()) {
            return $this->addRule('email');
        }

        $options = [];

        if ($filter) {
            $options[] = 'filter';
        }

        if ($filterUnicode) {
            $options[] = 'filter_unicode';
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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new Enum($type));
    }

    /**
     * Add exclude rule.
     *
     * @return $this
     */
    public function exclude(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('exclude_if', [$field, ...$values]);
    }

    /**
     * Add exclude_if rule.
     *
     * @param (Closure(): bool)|bool $condition
     *
     * @return $this
     */
    public function excludeIfRule(Closure|bool $condition): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new ExcludeIf($condition));
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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('exclude_unless', [$field, ...$values]);
    }

    /**
     * Add exclude_with rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function excludeWith(array $fields): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('exclude_with', $fields);
    }

    /**
     * Add exclude_without rule.
     *
     * @return $this
     */
    public function excludeWithout(string $field): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('exclude_without', [$field]);
    }

    /**
     * Add exists rule.
     *
     * @param array<int, string> $wheres
     *
     * @return $this
     */
    public function exists(string $table, string $column, array $wheres = []): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('exists', [$table, $column, ...$wheres]);
    }

    /**
     * Add file rule.
     *
     * @return $this
     */
    public function file(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('file');
    }

    /**
     * Add mimetypes rule.
     *
     * @param array<int, string> $mimetypes
     *
     * @return $this
     */
    public function mimetypes(array $mimetypes): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('mimetypes', $mimetypes);
    }

    /**
     * Add mimes rule.
     *
     * @param array<int, string> $mimes
     *
     * @return $this
     */
    public function mimes(array $mimes): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('mimes', $mimes);
    }

    /**
     * Add filled rule.
     *
     * @return $this
     */
    public function filled(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $this->filled = true;

        return $this;
    }

    /**
     * Add gt rule.
     *
     * @return $this
     */
    public function gt(string $field): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('gt', [$field]);
    }

    /**
     * Add gte rule.
     *
     * @return $this
     */
    public function gte(string $field): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('gte', [$field]);
    }

    /**
     * Add image rule.
     *
     * @param array<int, string> $mimeTypes
     *
     * @return $this
     */
    public function image(array $mimeTypes = ['image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/svg', 'image/webp', 'image/bmp', 'image/x-bmp', 'image/x-ms-bmp', 'image/heif', 'image/heic'], int $max = 10240): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->file()->mimetypes($mimeTypes)->max($max);
    }

    /**
     * Add video rule.
     *
     * @param array<int, string> $mimeTypes
     *
     * @return $this
     */
    public function video(array $mimeTypes = ['video/mp4', 'video/mpeg', 'video/ogg', 'video/quicktime', 'video/webm'], int $max = 10240): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->file()->mimetypes($mimeTypes)->max($max);
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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('in', $values);
    }

    /**
     * Add in rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function inRule(array $values): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new In($values));
    }

    /**
     * Add in_array rule.
     *
     * @return $this
     */
    public function inArray(string $field): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('in_array', [$field]);
    }

    /**
     * Add integer rule.
     *
     * @return $this
     */
    public function integer(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $rule = $this->addRule('integer');

        if ($min !== null) {
            \assert($max === null || $min <= $max);

            $rule->min($min);
        }

        if ($max !== null) {
            \assert($min === null || $min <= $max);

            $rule->max($max);
        }

        return $rule;
    }

    /**
     * Add ip rule.
     *
     * @return $this
     */
    public function ip(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('ip');
    }

    /**
     * Add ipv4 rule.
     *
     * @return $this
     */
    public function ipv4(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('ipv4');
    }

    /**
     * Add ipv6 rule.
     *
     * @return $this
     */
    public function ipv6(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('ipv6');
    }

    /**
     * Add mac_address rule.
     *
     * @return $this
     */
    public function macAddress(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('mac_address');
    }

    /**
     * Add json rule.
     *
     * @return $this
     */
    public function json(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('json');
    }

    /**
     * Add lt rule.
     *
     * @return $this
     */
    public function lt(string $field): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('lt', [$field]);
    }

    /**
     * Add lte rule.
     *
     * @return $this
     */
    public function lte(string $field): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('lte', [$field]);
    }

    /**
     * Add lowercase rule.
     *
     * @return $this
     */
    public function lowercase(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('lowercase');
    }

    /**
     * Add uppercase rule.
     *
     * @return $this
     */
    public function uppercase(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('uppercase');
    }

    /**
     * Add max rule.
     *
     * @return $this
     */
    public function max(int|float $max): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('max', [$max]);
    }

    /**
     * Add min rule.
     *
     * @return $this
     */
    public function min(int|float $min): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('min', [$min]);
    }

    /**
     * Add missing rule.
     *
     * @return $this
     */
    public function missing(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('missing');
    }

    /**
     * Add missing_if rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function missingIf(string $field, array $values): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('missing_if', [$field, ...$values]);
    }

    /**
     * Add missing_unless rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function missingUnless(string $field, array $values): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('missing_unless', [$field, ...$values]);
    }

    /**
     * Add missing_with rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function missingWith(array $fields): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('missing_with', $fields);
    }

    /**
     * Add missing_with_all rule.
     *
     * @param array<int, string> $fields
     *
     * @return $this
     */
    public function missingWithAll(array $fields): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('missing_with_all', $fields);
    }

    /**
     * Add multiple_of rule.
     *
     * @return $this
     */
    public function multipleOf(int|float $multipleOf): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('multiple_of', [$multipleOf]);
    }

    /**
     * Add not_if rule.
     *
     * @param array<int, mixed> $values
     *
     * @return $this
     */
    public function notIn(array $values): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('not_in', $values);
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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new NotIn($values));
    }

    /**
     * Add not_regex rule.
     *
     * @return $this
     */
    public function notRegex(string $pattern): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('not_regex', [$pattern]);
    }

    /**
     * Add nullable rule.
     *
     * @return $this
     */
    public function nullable(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $this->nullable = true;

        return $this;
    }

    /**
     * Add numeric rule.
     *
     * @return $this
     */
    public function numeric(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('numeric');
    }

    /**
     * Add password rule.
     *
     * @return $this
     */
    public function passwordRule(int $min): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new Password($min));
    }

    /**
     * Add password rule.
     *
     * @return $this
     */
    public function password(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(Password::default());
    }

    /**
     * Add present rule.
     *
     * @return $this
     */
    public function present(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('present');
    }

    /**
     * Add prohibited rule.
     *
     * @return $this
     */
    public function prohibited(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('prohibits', $fields);
    }

    /**
     * Add regex rule.
     *
     * @return $this
     */
    public function regex(string $pattern): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('regex', [$pattern]);
    }

    /**
     * Add required rule.
     *
     * @return $this
     */
    public function required(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $this->required = true;

        return $this;
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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('required_array_keys', $keys);
    }

    /**
     * Add same rule.
     *
     * @return $this
     */
    public function same(string $field): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('same', [$field]);
    }

    /**
     * Add size rule.
     *
     * @return $this
     */
    public function size(int|float $size): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('starts_with', $startsWith);
    }

    /**
     * Add string rule.
     *
     * @return $this
     */
    public function string(?int $max = null, ?int $min = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $rule = $this->addRule('string');

        if ($min !== null) {
            \assert($min >= 0);
            \assert($max === null || $min <= $max);

            $rule->min($min);
        }

        if ($max !== null) {
            \assert($max >= 0);
            \assert($min === null || $min <= $max);

            $rule->max($max);
        }

        return $rule;
    }

    /**
     * Add varchar rule.
     *
     * @return $this
     */
    public function varchar(?int $max = null, ?int $min = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $max ??= SchmeaBuilder::$defaultStringLength;

        \assert($max <= static::VARCHAR_MAX);

        return $this->string($max, $min);
    }

    /**
     * Add timezone rule.
     *
     * @return $this
     */
    public function timezone(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('timezone');
    }

    /**
     * Add unique rule.
     *
     * @param array<int, string> $wheres
     *
     * @return $this
     */
    public function unique(string $table, string $column, mixed $id = null, ?string $idColumn = null, array $wheres = []): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('unique', [$table, $column, $id, $idColumn, ...$wheres]);
    }

    /**
     * Add url rule.
     *
     * @return $this
     */
    public function url(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('url');
    }

    /**
     * Add uuid rule.
     *
     * @return $this
     */
    public function uuid(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('uuid');
    }

    /**
     * Add ulid rule.
     *
     * @return $this
     */
    public function ulid(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('ulid');
    }

    /**
     * Add closure rule.
     *
     * @param Closure(string, mixed, Closure(string): void): void $closure
     *
     * @return $this
     */
    public function closure(Closure $closure): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule($closure);
    }

    /**
     * Add required_if rule.
     *
     * @param (callable(): bool)|bool $condition
     *
     * @return $this
     */
    public function requiredIfRule(bool|callable $condition): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new RequiredIf($condition));
    }

    /**
     * Add sometimes rule.
     *
     * @return $this
     */
    public function sometimes(): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $this->sometimes = true;

        return $this;
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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

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
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('prohibited_without_all', $fields);
    }

    /**
     * Add strlen rule.
     *
     * @return $this
     */
    public function strlen(int $length): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        \assert($length >= 0);

        return $this->addRule('strlen', [$length]);
    }

    /**
     * Add strlen_max rule.
     *
     * @return $this
     */
    public function strlenMax(int $max): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        \assert($max >= 0);

        return $this->addRule('strlen_max', [$max]);
    }

    /**
     * Add strlen_min rule.
     *
     * @return $this
     */
    public function strlenMin(int $min): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        \assert($min >= 0);

        return $this->addRule('strlen_min', [$min]);
    }

    /**
     * Add prohibited if rule.
     *
     * @param (Closure(): bool)|bool $condition
     *
     * @return $this
     */
    public function prohibitedIfRule(bool|Closure $condition): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new ProhibitedIf($condition));
    }

    /**
     * Add char rules.
     *
     * @return $this
     */
    public function char(int $length): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule('string')->strlen($length);
    }

    /**
     * Add tiny text rules.
     *
     * @return $this
     */
    public function tinyText(?int $max = null, ?int $min = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $max ??= static::TINY_TEXT_MAX;

        \assert($max <= static::TINY_TEXT_MAX);

        $rule = $this->addRule('string')->strlenMax($max);

        if ($min !== null) {
            \assert($min <= $max);

            $rule->strlenMin($min);
        }

        return $rule;
    }

    /**
     * Add text rules.
     *
     * @return $this
     */
    public function text(?int $max = null, ?int $min = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $max ??= static::TEXT_MAX;

        \assert($max <= static::TEXT_MAX);

        $rule = $this->addRule('string')->strlenMax($max);

        if ($min !== null) {
            \assert($min <= $max);

            $rule->strlenMin($min);
        }

        return $rule;
    }

    /**
     * Add medium text rules.
     *
     * @return $this
     */
    public function mediumText(?int $max = null, ?int $min = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $max ??= static::MEDIUM_TEXT_MAX;

        \assert($max <= static::MEDIUM_TEXT_MAX);

        $rule = $this->addRule('string')->strlenMax($max);

        if ($min !== null) {
            \assert($min <= $max);

            $rule->strlenMin($min);
        }

        return $rule;
    }

    /**
     * Add long text rules.
     *
     * @return $this
     */
    public function longText(?int $max = null, ?int $min = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $max ??= static::LONG_TEXT_MAX;

        \assert($max <= static::LONG_TEXT_MAX);

        $rule = $this->addRule('string')->strlenMax($max);

        if ($min !== null) {
            \assert($min <= $max);

            $rule->strlenMin($min);
        }

        return $rule;
    }

    /**
     * Add tiny int rules.
     *
     * @return $this
     */
    public function tinyInt(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= static::TINY_INT_MIN;
        $max ??= static::TINY_INT_MAX;

        \assert($min >= static::TINY_INT_MIN);
        \assert($max <= static::TINY_INT_MAX);

        return $this->integer($min, $max);
    }

    /**
     * Add unsigned tiny int rules.
     *
     * @return $this
     */
    public function unsignedTinyInt(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= static::UNSIGNED_TINY_INT_MIN;
        $max ??= static::UNSIGNED_TINY_INT_MAX;

        \assert($min >= static::UNSIGNED_TINY_INT_MIN);
        \assert($max <= static::UNSIGNED_TINY_INT_MAX);

        return $this->integer($min, $max);
    }

    /**
     * Add small int rules.
     *
     * @return $this
     */
    public function smallInt(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= static::SMALL_INT_MIN;
        $max ??= static::SMALL_INT_MAX;

        \assert($min >= static::SMALL_INT_MIN);
        \assert($max <= static::SMALL_INT_MAX);

        return $this->integer($min, $max);
    }

    /**
     * Add unsigned small int rules.
     *
     * @return $this
     */
    public function unsignedSmallInt(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= static::UNSIGNED_SMALL_INT_MIN;
        $max ??= static::UNSIGNED_SMALL_INT_MAX;

        \assert($min >= static::UNSIGNED_SMALL_INT_MIN);
        \assert($max <= static::UNSIGNED_SMALL_INT_MAX);

        return $this->integer($min, $max);
    }

    /**
     * Add medium int rules.
     *
     * @return $this
     */
    public function mediumInt(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= static::MEDIUM_INT_MIN;
        $max ??= static::MEDIUM_INT_MAX;

        \assert($min >= static::MEDIUM_INT_MIN);
        \assert($max <= static::MEDIUM_INT_MAX);

        return $this->integer($min, $max);
    }

    /**
     * Add unsigned medium int rules.
     *
     * @return $this
     */
    public function unsignedMediumInt(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= static::UNSIGNED_MEDIUM_INT_MIN;
        $max ??= static::UNSIGNED_MEDIUM_INT_MAX;

        \assert($min >= static::UNSIGNED_MEDIUM_INT_MIN);
        \assert($max <= static::UNSIGNED_MEDIUM_INT_MAX);

        return $this->integer($min, $max);
    }

    /**
     * Add int rules.
     *
     * @return $this
     */
    public function int(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= static::INT_MIN;
        $max ??= static::INT_MAX;

        \assert($min >= static::INT_MIN);
        \assert($max <= static::INT_MAX);

        return $this->integer($min, $max);
    }

    /**
     * Add unsigned int rules.
     *
     * @return $this
     */
    public function unsignedInt(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= static::UNSIGNED_INT_MIN;
        $max ??= static::UNSIGNED_INT_MAX;

        \assert($min >= static::UNSIGNED_INT_MIN);
        \assert($max <= static::UNSIGNED_INT_MAX);

        return $this->integer($min, $max);
    }

    /**
     * Add big int rules.
     *
     * @return $this
     */
    public function bigInt(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= static::BIG_INT_MIN;
        $max ??= static::BIG_INT_MAX;

        \assert($min >= static::BIG_INT_MIN);
        \assert($max <= static::BIG_INT_MAX);

        return $this->integer($min, $max);
    }

    /**
     * Add unsigned big int rules.
     *
     * @return $this
     */
    public function unsignedBigInt(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= static::UNSIGNED_BIG_INT_MIN;
        $max ??= static::UNSIGNED_BIG_INT_MAX;

        \assert($min >= static::UNSIGNED_BIG_INT_MIN);
        \assert($max <= static::UNSIGNED_BIG_INT_MAX);

        return $this->integer($min, $max);
    }

    /**
     * Add unsigned rules.
     *
     * @return $this
     */
    public function unsigned(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->unsignedBigInt($min, $max);
    }

    /**
     * Add signed rules.
     *
     * @return $this
     */
    public function signed(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->bigInt($min, $max);
    }

    /**
     * Add positive rules.
     *
     * @return $this
     */
    public function positive(?int $min = null, ?int $max = null): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        $min ??= 1;

        \assert($min >= 1);

        return $this->unsigned($min, $max);
    }

    /**
     * Add callback rule.
     *
     * @param Closure(mixed, mixed=): bool $callback
     *
     * @return $this
     */
    public function callback(Closure $callback, string $message = 'validation.regex'): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new CallbackRule($callback, $message));
    }

    /**
     * Add query rule.
     *
     * @template T of Relation|Builder
     *
     * @param T $query
     * @param (Closure(T, mixed=, mixed=): void)|null $callback
     *
     * @return $this
     */
    public function query(Relation|Builder $query, ?Closure $callback = null, string $message = 'validation.exists'): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new CallbackRule(static function (mixed $value, mixed $attribute = null) use ($query, $callback): bool {
            $query = clone $query;

            $eloquent = $query instanceof Relation ? $query->getQuery() : $query;

            if ($callback !== null) {
                $callback($query, $value, $attribute);
            }

            return $eloquent->toBase()->exists();
        }, $message));
    }

    /**
     * Add query key rule.
     *
     * @template T of Relation|Builder
     *
     * @param T $query
     * @param (Closure(T, mixed=, mixed=): void)|null $callback
     *
     * @return $this
     */
    public function queryKey(Relation|Builder $query, ?Closure $callback = null, string $message = 'validation.exists'): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new CallbackRule(static function (mixed $value, mixed $attribute = null) use ($query, $callback): bool {
            $query = clone $query;

            $eloquent = $query instanceof Relation ? $query->getQuery() : $query;

            $eloquent->whereKey($value);

            if ($callback !== null) {
                $callback($query, $value, $attribute);
            }

            return $eloquent->toBase()->exists();
        }, $message));
    }

    /**
     * Add builder rule.
     *
     * @template T of Builder
     *
     * @param Closure(mixed=, mixed=): T $callback
     *
     * @return $this
     */
    public function builder(Closure $callback, string $message = 'validation.exists'): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new CallbackRule(static function (mixed $value, mixed $attribute = null) use ($callback): bool {
            $builder = $callback($value, $attribute);

            return $builder->toBase()->exists();
        }, $message));
    }

    /**
     * Add builder key rule.
     *
     * @template T of Builder
     *
     * @param Closure(mixed=, mixed=): T $callback
     *
     * @return $this
     */
    public function builderKey(Closure $callback, string $message = 'validation.exists'): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new CallbackRule(static function (mixed $value, mixed $attribute = null) use ($callback): bool {
            $builder = $callback($value, $attribute);

            return $builder->whereKey($value)->toBase()->exists();
        }, $message));
    }

    /**
     * Add not builder rule.
     *
     * @template T of Builder
     *
     * @param Closure(mixed=, mixed=): T $callback
     *
     * @return $this
     */
    public function notBuilder(Closure $callback, string $message = 'validation.exists'): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new CallbackRule(static function (mixed $value, mixed $attribute = null) use ($callback): bool {
            $builder = $callback($value, $attribute);

            return ! $builder->toBase()->exists();
        }, $message));
    }

    /**
     * Add not builder key rule.
     *
     * @template T of Builder
     *
     * @param Closure(mixed=, mixed=): T $callback
     *
     * @return $this
     */
    public function notBuilderKey(Closure $callback, string $message = 'validation.exists'): static
    {
        if ($this->skipNext) {
            $this->skipNext = false;

            return $this;
        }

        return $this->addRule(new CallbackRule(static function (mixed $value, mixed $attribute = null) use ($callback): bool {
            $builder = $callback($value, $attribute);

            return ! $builder->whereKey($value)->toBase()->exists();
        }, $message));
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $rules = [];

        if ($this->bail) {
            $rules[] = 'bail';
        }

        if ($this->sometimes) {
            $rules[] = 'sometimes';
        }

        if ($this->nullable) {
            $rules[] = 'nullable';
        }

        if ($this->filled) {
            $rules[] = 'filled';
        }

        if ($this->required) {
            $rules[] = 'required';
        }

        return \array_merge($rules, $this->rules);
    }

    /**
     * Format arguments.
     *
     * @param array<int, mixed> $arguments
     */
    protected function formatArguments(array $arguments): string
    {
        return \implode(',', $arguments);
    }
}
