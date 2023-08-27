<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\ValidatedInput as IlluminateValidatedInput;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Exceptions\Panicker;
use Tomchochola\Laratchi\Support\AssertTrait;
use Tomchochola\Laratchi\Support\ParserTrait;
use Tomchochola\Laratchi\Support\ParseTrait;

class ValidatedInput extends IlluminateValidatedInput
{
    use AssertTrait;
    use ParserTrait;
    use ParseTrait;

    /**
     * @inheritDoc
     *
     * @param array<mixed> $input
     */
    final public function __construct(array $input)
    {
        parent::__construct($input);
    }

    /**
     * String resolver.
     */
    public function string(string $key, ?string $default = null): ?string
    {
        $value = $this->get($key, $default);

        if ($value === null || \is_string($value)) {
            return $value;
        }

        $value = \filter_var($value);

        if ($value === false) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Mandatory string resolver.
     */
    public function mustString(string $key, ?string $default = null): string
    {
        $value = $this->string($key, $default);

        if ($value === null) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Bool resolver.
     */
    public function bool(string $key, ?bool $default = null): ?bool
    {
        $value = $this->get($key, $default);

        if ($value === null || \is_bool($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);

        if ($value === null) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Mandatory bool resolver.
     */
    public function mustBool(string $key, ?bool $default = null): bool
    {
        $value = $this->bool($key, $default);

        if ($value === null) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Int resolver.
     */
    public function int(string $key, ?int $default = null): ?int
    {
        $value = $this->get($key, $default);

        if ($value === null || \is_int($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_INT);

        if ($value === false) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Mandatory int resolver.
     */
    public function mustInt(string $key, ?int $default = null): int
    {
        $value = $this->int($key, $default);

        if ($value === null) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Float resolver.
     */
    public function float(string $key, ?float $default = null): ?float
    {
        $value = $this->get($key, $default);

        if ($value === null || \is_float($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_FLOAT);

        if ($value === false) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Mandatory float resolver.
     */
    public function mustFloat(string $key, ?float $default = null): float
    {
        $value = $this->float($key, $default);

        if ($value === null) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * File resolver.
     */
    public function file(string $key, ?UploadedFile $default = null): ?UploadedFile
    {
        $value = $this->get($key, $default);

        if ($value === null || $value instanceof UploadedFile) {
            return $value;
        }

        Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
    }

    /**
     * Mandatory file resolver.
     */
    public function mustFile(string $key, ?UploadedFile $default = null): UploadedFile
    {
        $value = $this->file($key, $default);

        if ($value === null) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Array resolver.
     *
     * @param array<mixed>|null $default
     *
     * @return array<mixed>|null
     */
    public function array(string $key, ?array $default = null): ?array
    {
        $value = $this->get($key, $default);

        if ($value === null || \is_array($value)) {
            return $value;
        }

        Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
    }

    /**
     * Mandatory array resolver.
     *
     * @param array<mixed>|null $default
     *
     * @return array<mixed>
     */
    public function mustArray(string $key, ?array $default): array
    {
        $value = $this->array($key, $default);

        if ($value === null) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Date resolver.
     */
    public function date(string $key, ?Carbon $default = null, ?string $format = null, ?string $tz = null): ?Carbon
    {
        $value = $this->get($key, $default);

        if ($value === null || $value instanceof Carbon) {
            return $value;
        }

        $value = \filter_var($value);

        if ($value === false || $value === '') {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        if ($format === null) {
            return resolveDate()
                ->parse($value, $tz)
                ->setTimezone(Config::inject()->appTimezone());
        }

        $value = resolveDate()->createFromFormat($format, $value, $tz);

        if ($value === false) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value->setTimezone(Config::inject()->appTimezone());
    }

    /**
     * Mandatory date resolver.
     */
    public function mustDate(string $key, ?Carbon $default = null, ?string $format = null, ?string $tz = null): Carbon
    {
        $value = $this->date($key, $default, $format, $tz);

        if ($value === null) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Resolve value from data array.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->input, $key) ?? $default;
    }

    /**
     * Make new validated input from given data.
     *
     * @param array<mixed> $data
     */
    public function newValidatedInput(array $data): static
    {
        return new static($data);
    }

    /**
     * Make new validated input from given key.
     *
     * @param array<mixed>|null $default
     */
    public function validatedInput(string $key, ?array $default): static
    {
        return new static($this->mustArray($key, $default));
    }

    /**
     * Make new validated inputs from given key.
     *
     * @param array<mixed>|null $default
     *
     * @return array<int, static>
     */
    public function validatedInputs(string $key, ?array $default): array
    {
        $validatedInputs = [];

        $data = $this->mustArray($key, $default);

        foreach ($data as $validatedInput) {
            if (! \is_array($validatedInput)) {
                $validatedInputs[] = new static(['value' => $validatedInput]);
            } else {
                $validatedInputs[] = new static($validatedInput);
            }
        }

        return $validatedInputs;
    }

    /**
     * Attribute is null.
     */
    public function isNull(string $key): bool
    {
        return $this->get($key) === null;
    }

    /**
     * Attribute is not null.
     */
    public function isNotNull(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * String or int resolver.
     */
    public function stringOrInt(string $key, string|int|null $default = null): string|int|null
    {
        $value = $this->get($key, $default);

        if ($value === null || \is_string($value) || \is_int($value)) {
            return $value;
        }

        $filtered = \filter_var($value, \FILTER_VALIDATE_INT);

        if ($filtered !== false) {
            return $filtered;
        }

        $filtered = \filter_var($value);

        if ($filtered === false) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $filtered;
    }

    /**
     * Mandatory string or int resolver.
     */
    public function mustStringOrInt(string $key, string|int|null $default = null): string|int
    {
        $value = $this->stringOrInt($key, $default);

        if ($value === null) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('key', 'value', 'default'));
        }

        return $value;
    }

    /**
     * Int or string resolver.
     */
    public function intOrString(string $key, string|int|null $default = null): string|int|null
    {
        return $this->stringOrInt($key, $default);
    }

    /**
     * Mandatory int or string resolver..
     */
    public function mustIntOrString(string $key, string|int|null $default = null): string|int
    {
        return $this->mustStringOrInt($key, $default);
    }

    /**
     * Mixed getter.
     */
    public function mixed(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->input;
        }

        return Arr::get($this->input, $key);
    }
}
