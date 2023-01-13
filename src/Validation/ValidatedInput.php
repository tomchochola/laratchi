<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\ValidatedInput as IlluminateValidatedInput;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidatedInput extends IlluminateValidatedInput
{
    /**
     * What status is thrown on invalid cast.
     */
    public static int $castFailedStatus = 400;

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

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value);

        if ($value === false) {
            $this->throw("[{$key}] is not string or null");
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
            $this->throw("[{$key}] is not string");
        }

        return $value;
    }

    /**
     * Bool resolver.
     */
    public function bool(string $key, ?bool $default = null): ?bool
    {
        $value = $this->get($key, $default);

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);

        if ($value === null) {
            $this->throw("[{$key}] is not bool or null");
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
            $this->throw("[{$key}] is not bool");
        }

        return $value;
    }

    /**
     * Int resolver.
     */
    public function int(string $key, ?int $default = null): ?int
    {
        $value = $this->get($key, $default);

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_INT);

        if ($value === false) {
            $this->throw("[{$key}] is not int or null");
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
            $this->throw("[{$key}] is not int");
        }

        return $value;
    }

    /**
     * Float resolver.
     */
    public function float(string $key, ?float $default = null): ?float
    {
        $value = $this->get($key, $default);

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_FLOAT);

        if ($value === false) {
            $this->throw("[{$key}] is not float or null");
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
            $this->throw("[{$key}] is not float");
        }

        return $value;
    }

    /**
     * File resolver.
     */
    public function file(string $key, ?UploadedFile $default = null): ?UploadedFile
    {
        $value = $this->get($key, $default);

        if ($value === null) {
            return null;
        }

        if (! $value instanceof UploadedFile) {
            $this->throw("[{$key}] is not file or null");
        }

        return $value;
    }

    /**
     * Mandatory file resolver.
     */
    public function mustFile(string $key, ?UploadedFile $default = null): UploadedFile
    {
        $value = $this->file($key, $default);

        if ($value === null) {
            $this->throw("[{$key}] is not file");
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

        if ($value === null) {
            return null;
        }

        if (! \is_array($value)) {
            $this->throw("[{$key}] is not array or null");
        }

        return $value;
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
        $value = $this->array($key, $default);

        if ($value === null) {
            $this->throw("[{$key}] is not array");
        }

        return $value;
    }

    /**
     * Date resolver.
     */
    public function date(string $key, ?Carbon $default = null, ?string $format = null, ?string $tz = null): ?Carbon
    {
        $value = $this->get($key, $default);

        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        $value = \filter_var($value);

        if ($value === false) {
            $this->throw("[{$key}] is not date or null");
        }

        if ($value === '') {
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
        $value = $this->date($key, $default, $format, $tz);

        if ($value === null) {
            $this->throw("[{$key}] is not date");
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
    public function validatedInput(string $key, ?array $default = null): static
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
    public function validatedInputs(string $key, ?array $default = null): array
    {
        $validatedInputs = [];

        $data = $this->mustArray($key, $default);

        foreach ($data as $validatedInput) {
            \assert(\is_array($validatedInput));

            $validatedInputs[] = new static($validatedInput);
        }

        return $validatedInputs;
    }

    /**
     * Throw error.
     */
    protected function throw(string $message): never
    {
        $logicException = new LogicException($message);

        if (static::$castFailedStatus === 0) {
            throw $logicException;
        }

        \assert(static::$castFailedStatus >= 400 && static::$castFailedStatus <= 599);

        throw new HttpException(static::$castFailedStatus, previous: $logicException);
    }
}
