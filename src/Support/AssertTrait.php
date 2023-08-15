<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use BackedEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use UnexpectedValueException;

trait AssertTrait
{
    /**
     * Assert string.
     */
    public function assertString(string $key): string
    {
        $value = $this->mixed($key);

        \assert(\is_string($value), \sprintf("key:[{$key}] value:[%s] is not string on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert nullable string.
     */
    public function assertNullableString(string $key): ?string
    {
        $value = $this->mixed($key);

        \assert($value === null || \is_string($value), \sprintf("key:[{$key}] value:[%s] is not string or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert bool.
     */
    public function assertBool(string $key): bool
    {
        $value = $this->mixed($key);

        \assert(\is_bool($value), \sprintf("key:[{$key}] value:[%s] is not bool on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert nullable bool.
     */
    public function assertNullableBool(string $key): ?bool
    {
        $value = $this->mixed($key);

        \assert($value === null || \is_bool($value), \sprintf("key:[{$key}] value:[%s] is not bool or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert int.
     */
    public function assertInt(string $key): int
    {
        $value = $this->mixed($key);

        \assert(\is_int($value), \sprintf("key:[{$key}] value:[%s] is not int on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert nullable int.
     */
    public function assertNullableInt(string $key): ?int
    {
        $value = $this->mixed($key);

        \assert($value === null || \is_int($value), \sprintf("key:[{$key}] value:[%s] is not int or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert float.
     */
    public function assertFloat(string $key): float
    {
        $value = $this->mixed($key);

        \assert(\is_float($value), \sprintf("key:[{$key}] value:[%s] is not float on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert nullable float.
     */
    public function assertNullableFloat(string $key): ?float
    {
        $value = $this->mixed($key);

        \assert($value === null || \is_float($value), \sprintf("key:[{$key}] value:[%s] is not float or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert array.
     *
     * @return array<mixed>
     */
    public function assertArray(string $key): array
    {
        $value = $this->mixed($key);

        \assert(\is_array($value), \sprintf("key:[{$key}] value:[%s] is not array on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert nullable array.
     *
     * @return array<mixed>|null
     */
    public function assertNullableArray(string $key): ?array
    {
        $value = $this->mixed($key);

        \assert($value === null || \is_array($value), \sprintf("key:[{$key}] value:[%s] is not array or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert file.
     */
    public function assertFile(string $key): UploadedFile
    {
        $value = $this->mixed($key);

        \assert($value instanceof UploadedFile, \sprintf("key:[{$key}] value:[%s] is not file on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert nullable file.
     */
    public function assertNullableFile(string $key): ?UploadedFile
    {
        $value = $this->mixed($key);

        \assert($value === null || $value instanceof UploadedFile, \sprintf("key:[{$key}] value:[%s] is not file or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert carbon.
     */
    public function assertCarbon(string $key): Carbon
    {
        $value = $this->mixed($key);

        \assert($value instanceof Carbon, \sprintf("key:[{$key}] value:[%s] is not carbon on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert nullable carbon.
     */
    public function assertNullableCarbon(string $key): ?Carbon
    {
        $value = $this->mixed($key);

        \assert($value === null || $value instanceof Carbon, \sprintf("key:[{$key}] value:[%s] is not carbon or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert object.
     */
    public function assertObject(string $key): object
    {
        $value = $this->mixed($key);

        \assert(\is_object($value), \sprintf("key:[{$key}] value:[%s] is not object on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert nullable object.
     */
    public function assertNullableObject(string $key): ?object
    {
        $value = $this->mixed($key);

        \assert($value === null || \is_object($value), \sprintf("key:[{$key}] value:[%s] is not object or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert scalar.
     */
    public function assertScalar(string $key): int|float|string|bool
    {
        $value = $this->mixed($key);

        \assert(\is_scalar($value), \sprintf("key:[{$key}] value:[%s] is not scalar on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert nullable scalar.
     */
    public function assertNullableScalar(string $key): int|float|string|bool|null
    {
        $value = $this->mixed($key);

        \assert($value === null || \is_scalar($value), \sprintf("key:[{$key}] value:[%s] is not scalar or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    public function assertEnum(string $key, string $enum): BackedEnum
    {
        $value = $this->mixed($key);

        \assert($value instanceof $enum, \sprintf("key:[{$key}] value:[%s] is not class:[{$enum}] instance on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert nullable enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    public function assertNullableEnum(string $key, string $enum): ?BackedEnum
    {
        $value = $this->mixed($key);

        \assert(
            $value === null || $value instanceof $enum,
            \sprintf("key:[{$key}] value:[%s] is not class:[{$enum}] instance or null on:[%s]", \get_debug_type($value), static::class),
        );

        return $value;
    }

    /**
     * Assert instance.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function assertInstance(string $key, string $class): object
    {
        $value = $this->mixed($key);

        if ($value instanceof $class) {
            return $value;
        }

        throw new UnexpectedValueException(\sprintf("key:[{$key}] value:[%s] is not class:[{$class}] instance on:[%s]", \get_debug_type($value), static::class));
    }

    /**
     * Assert nullable instance.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    public function assertNullableInstance(string $key, string $class): ?object
    {
        $value = $this->mixed($key);

        if ($value === null || $value instanceof $class) {
            return $value;
        }

        throw new UnexpectedValueException(\sprintf("key:[{$key}] value:[%s] is not class:[{$class}] instance or null on:[%s]", \get_debug_type($value), static::class));
    }

    /**
     * Assert a.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return class-string<T>
     */
    public function assertA(string $key, string $class): string
    {
        $value = $this->mixed($key);

        if (\is_string($value) && \is_a($value, $class, true)) {
            return $value;
        }

        throw new UnexpectedValueException(\sprintf("key:[{$key}] value:[%s] is not class:[{$class}] class-string on:[%s]", \get_debug_type($value), static::class));
    }

    /**
     * Assert nullable a.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return class-string<T>|null
     */
    public function assertNullableA(string $key, string $class): ?string
    {
        $value = $this->mixed($key);

        if ($value === null) {
            return $value;
        }

        if (\is_string($value) && \is_a($value, $class, true)) {
            return $value;
        }

        throw new UnexpectedValueException(\sprintf("key:[{$key}] value:[%s] is not class:[{$class}] class-string on:[%s]", \get_debug_type($value), static::class));
    }

    /**
     * Assert in.
     *
     * @template T
     *
     * @param array<T> $enum
     *
     * @return T
     */
    public function assertIn(string $key, array $enum): mixed
    {
        $value = $this->mixed($key);

        if (\in_array($value, $enum, true)) {
            return $value;
        }

        throw new UnexpectedValueException(\sprintf("key:[{$key}] value:[%s] is not in array on:[%s]", \get_debug_type($value), static::class));
    }

    /**
     * Assert not null.
     *
     * @return string|int|float|bool|object|array<mixed>
     */
    public function assertNotNull(string $key): string|int|float|bool|object|array
    {
        $value = $this->mixed($key);

        assert(\is_string($value) || \is_int($value) || \is_float($value) || \is_bool($value) || \is_object($value) || \is_array($value), \sprintf("key:[{$key}] value:[%s] is null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Assert null.
     */
    public function assertNull(string $key): mixed
    {
        $value = $this->mixed($key);

        if ($value !== null) {
            throw new UnexpectedValueException(\sprintf("key:[{$key}] value:[%s] is not null on:[%s]", \get_debug_type($value), static::class));
        }

        return $value;
    }
}
