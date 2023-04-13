<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\ValidatedInput as IlluminateValidatedInput;

class AllInput extends IlluminateValidatedInput
{
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
            return null;
        }

        return $value;
    }

    /**
     * Mandatory string resolver.
     */
    public function mustString(string $key, ?string $default = null): string
    {
        return $this->string($key, $default) ?? '';
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

        return \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);
    }

    /**
     * Mandatory bool resolver.
     */
    public function mustBool(string $key, ?bool $default = null): bool
    {
        return $this->bool($key, $default) ?? false;
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
            return null;
        }

        return $value;
    }

    /**
     * Mandatory int resolver.
     */
    public function mustInt(string $key, ?int $default = null): int
    {
        return $this->int($key, $default) ?? 0;
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
            return null;
        }

        return $value;
    }

    /**
     * Mandatory float resolver.
     */
    public function mustFloat(string $key, ?float $default = null): float
    {
        return $this->float($key, $default) ?? 0.0;
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

        return null;
    }

    /**
     * Mandatory file resolver.
     */
    public function mustFile(string $key, ?UploadedFile $default = null): UploadedFile
    {
        return $this->file($key, $default) ?? UploadedFile::createFromBase(UploadedFile::fake()->create($key));
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

        return null;
    }

    /**
     * Mandatory array resolver.
     *
     * @param array<mixed>|null $default
     *
     * @return array<mixed>
     */
    public function mustArray(string $key, ?array $default = null): array
    {
        return $this->array($key, $default) ?? [];
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
            return null;
        }

        if ($format === null) {
            return resolveDate()->parse($value, $tz);
        }

        $value = resolveDate()->createFromFormat($format, $value, $tz);

        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * Mandatory date resolver.
     */
    public function mustDate(string $key, ?Carbon $default = null, ?string $format = null, ?string $tz = null): Carbon
    {
        return $this->date($key, $default, $format, $tz) ?? resolveDate()->now();
    }

    /**
     * Resolve value from data array.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->input, $key) ?? $default;
    }

    /**
     * Make new all input from given data.
     *
     * @param array<mixed> $data
     */
    public function newAllInput(array $data): static
    {
        return new static($data);
    }

    /**
     * Make new all input from given key.
     *
     * @param array<mixed>|null $default
     */
    public function allInput(string $key, ?array $default = null): static
    {
        return new static($this->mustArray($key, $default));
    }

    /**
     * Make new all inputs from given key.
     *
     * @param array<mixed>|null $default
     *
     * @return array<int, static>
     */
    public function allInputs(string $key, ?array $default = null): array
    {
        $allInputs = [];

        $data = $this->mustArray($key, $default);

        foreach ($data as $allInput) {
            if (! \is_array($allInput)) {
                $allInputs[] = new static(['value' => $allInput]);
            } else {
                $allInputs[] = new static($allInput);
            }
        }

        return $allInputs;
    }
}
