<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use BackedEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use ReflectionEnum;
use stdClass;
use Tomchochola\Laratchi\Config\Config;

trait ParseTrait
{
    /**
     * Parse string.
     */
    public function parseString(string $key): string
    {
        return $this->parseNullableString($key) ?? '';
    }

    /**
     * Parse nullable string.
     */
    public function parseNullableString(string $key): ?string
    {
        $value = $this->mixed($key);

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
     * Must parse string.
     */
    public function mustParseString(string $key): string
    {
        $value = $this->mustParseNullableString($key);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not string on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Must parse nullable string.
     */
    public function mustParseNullableString(string $key): ?string
    {
        $value = $this->mixed($key);

        if ($value === null || \is_string($value)) {
            return $value;
        }

        $value = \filter_var($value);

        \assert($value !== false, \sprintf("key:[{$key}] value:[%s] is not string or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Parse bool.
     */
    public function parseBool(string $key): bool
    {
        return $this->parseNullableBool($key) ?? false;
    }

    /**
     * Parse nullable bool.
     */
    public function parseNullableBool(string $key): ?bool
    {
        $value = $this->mixed($key);

        if ($value === null || \is_bool($value)) {
            return $value;
        }

        return \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);
    }

    /**
     * Must parse bool.
     */
    public function mustParseBool(string $key): bool
    {
        $value = $this->mustParseNullableBool($key);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not bool on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Must parse nullable bool.
     */
    public function mustParseNullableBool(string $key): ?bool
    {
        $value = $this->mixed($key);

        if ($value === null || \is_bool($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not bool or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Parse int.
     */
    public function parseInt(string $key): int
    {
        return $this->parseNullableInt($key) ?? 0;
    }

    /**
     * Parse nullable int.
     */
    public function parseNullableInt(string $key): ?int
    {
        $value = $this->mixed($key);

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
     * Must parse int.
     */
    public function mustParseInt(string $key): int
    {
        $value = $this->mustParseNullableInt($key);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not int on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Must parse nullable int.
     */
    public function mustParseNullableInt(string $key): ?int
    {
        $value = $this->mixed($key);

        if ($value === null || \is_int($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_INT);

        \assert($value !== false, \sprintf("key:[{$key}] value:[%s] is not int or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Parse float.
     */
    public function parseFloat(string $key): float
    {
        return $this->parseNullableFloat($key) ?? 0.0;
    }

    /**
     * Parse nullable float.
     */
    public function parseNullableFloat(string $key): ?float
    {
        $value = $this->mixed($key);

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
     * Must parse float.
     */
    public function mustParseFloat(string $key): float
    {
        $value = $this->mustParseNullableFloat($key);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not float on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Must parse nullable float.
     */
    public function mustParseNullableFloat(string $key): ?float
    {
        $value = $this->mixed($key);

        if ($value === null || \is_int($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_FLOAT);

        \assert($value !== false, \sprintf("key:[{$key}] value:[%s] is not float or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Parse array.
     *
     * @return array<mixed>
     */
    public function parseArray(string $key): array
    {
        return $this->parseNullableArray($key) ?? [];
    }

    /**
     * Parse nullable array.
     *
     * @return array<mixed>|null
     */
    public function parseNullableArray(string $key): ?array
    {
        $value = $this->mixed($key);

        if ($value === null || \is_array($value)) {
            return $value;
        }

        if (\is_object($value)) {
            return \get_object_vars($value);
        }

        return null;
    }

    /**
     * Must parse array.
     *
     * @return array<mixed>
     */
    public function mustParseArray(string $key): array
    {
        $value = $this->mustParseNullableArray($key);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not array on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Must parse nullable array.
     *
     * @return array<mixed>|null
     */
    public function mustParseNullableArray(string $key): ?array
    {
        $value = $this->mixed($key);

        if (\is_object($value)) {
            return \get_object_vars($value);
        }

        \assert($value === null || \is_array($value), \sprintf("key:[{$key}] value:[%s] is not array or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Parse file.
     */
    public function parseFile(string $key): UploadedFile
    {
        return $this->parseNullableFile($key) ?? UploadedFile::createFromBase(UploadedFile::fake()->create(assertString(\tempnam(\sys_get_temp_dir(), 'php'))));
    }

    /**
     * Parse nullable file.
     */
    public function parseNullableFile(string $key): ?UploadedFile
    {
        $value = $this->mixed($key);

        if ($value === null || $value instanceof UploadedFile) {
            return $value;
        }

        return null;
    }

    /**
     * Must parse file.
     */
    public function mustParseFile(string $key): UploadedFile
    {
        $value = $this->mustParseNullableFile($key);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not file on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Must parse nullable file.
     */
    public function mustParseNullableFile(string $key): ?UploadedFile
    {
        $value = $this->mixed($key);

        \assert($value === null || $value instanceof UploadedFile, \sprintf("key:[{$key}] value:[%s] is not file or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Parse carbon.
     */
    public function parseCarbon(string $key, ?string $format = null, ?string $tz = null): Carbon
    {
        return $this->parseNullableCarbon($key, $format, $tz) ?? resolveNow();
    }

    /**
     * Parse nullable carbon.
     */
    public function parseNullableCarbon(string $key, ?string $format = null, ?string $tz = null): ?Carbon
    {
        $value = $this->mixed($key);

        if ($value === null || $value instanceof Carbon) {
            return $value;
        }

        $value = \filter_var($value);

        if ($value === false || $value === '') {
            return null;
        }

        if ($format === null) {
            return resolveDate()
                ->parse($value, $tz)
                ->setTimezone((new Config())->appTimezone());
        }

        $value = resolveDate()->createFromFormat($format, $value, $tz);

        if ($value === false) {
            return null;
        }

        return $value->setTimezone((new Config())->appTimezone());
    }

    /**
     * Must parse carbon.
     */
    public function mustParseCarbon(string $key, ?string $format = null, ?string $tz = null): Carbon
    {
        $value = $this->mustParseNullableCarbon($key, $format, $tz);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not carbon on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Must parse nullable carbon.
     */
    public function mustParseNullableCarbon(string $key, ?string $format = null, ?string $tz = null): ?Carbon
    {
        $value = $this->mixed($key);

        if ($value === null || $value instanceof Carbon) {
            return $value;
        }

        $value = \filter_var($value);

        \assert($value !== false && $value !== '', \sprintf("key:[{$key}] value:[%s] is not carbon on:[%s]", \get_debug_type($value), static::class));

        if ($format === null) {
            return resolveDate()
                ->parse($value, $tz)
                ->setTimezone((new Config())->appTimezone());
        }

        $value = resolveDate()->createFromFormat($format, $value, $tz);

        \assert($value !== false, \sprintf("key:[{$key}] value:[%s] is not carbon on:[%s]", \get_debug_type($value), static::class));

        return $value->setTimezone((new Config())->appTimezone());
    }

    /**
     * Parse object.
     */
    public function parseObject(string $key): object
    {
        return $this->parseNullableObject($key) ?? new stdClass();
    }

    /**
     * Parse nullable object.
     */
    public function parseNullableObject(string $key): ?object
    {
        $value = $this->mixed($key);

        if ($value === null || \is_object($value)) {
            return $value;
        }

        if (\is_array($value)) {
            return (object) $value;
        }

        return null;
    }

    /**
     * Must parse object.
     */
    public function mustParseObject(string $key): object
    {
        $value = $this->mustParseNullableObject($key);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not object on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Must parse nullable object.
     */
    public function mustParseNullableObject(string $key): ?object
    {
        $value = $this->mixed($key);

        if (\is_array($value)) {
            return (object) $value;
        }

        \assert($value === null || \is_object($value), \sprintf("key:[{$key}] value:[%s] is not object or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Parse scalar.
     */
    public function parseScalar(string $key): string|bool|int|float
    {
        return $this->parseNullableScalar($key) ?? '';
    }

    /**
     * Parse nullable scalar.
     */
    public function parseNullableScalar(string $key): string|bool|int|float|null
    {
        $value = $this->mixed($key);

        if ($value === null || \is_scalar($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Must parse scalar.
     */
    public function mustParseScalar(string $key): string|bool|int|float
    {
        $value = $this->mustParseNullableScalar($key);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not scalar on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Must parse nullable scalar.
     */
    public function mustParseNullableScalar(string $key): string|bool|int|float|null
    {
        $value = $this->mixed($key);

        \assert($value === null || \is_scalar($value), \sprintf("key:[{$key}] value:[%s] is not scalar or null on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Parse enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    public function parseEnum(string $key, string $enum): BackedEnum
    {
        return $this->parseNullableEnum($key, $enum) ?? $enum::cases()[0];
    }

    /**
     * Parse nullable enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    public function parseNullableEnum(string $key, string $enum): ?BackedEnum
    {
        if ((string) (new ReflectionEnum($enum))->getBackingType() === 'int') {
            $value = $this->parseNullableInt($key);
        } else {
            $value = $this->parseNullableString($key);
        }

        if ($value === null) {
            return $value;
        }

        return $enum::tryFrom($value);
    }

    /**
     * Must parse enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    public function mustParseEnum(string $key, string $enum): BackedEnum
    {
        $value = $this->mustParseNullableEnum($key, $enum);

        \assert($value !== null, \sprintf("key:[{$key}] value:[%s] is not class:[{$enum}] enum on:[%s]", \get_debug_type($value), static::class));

        return $value;
    }

    /**
     * Must parse nullable enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    public function mustParseNullableEnum(string $key, string $enum): ?BackedEnum
    {
        if ((string) (new ReflectionEnum($enum))->getBackingType() === 'int') {
            $value = $this->mustParseNullableInt($key);
        } else {
            $value = $this->mustParseNullableString($key);
        }

        if ($value === null) {
            return $value;
        }

        return $enum::from($value);
    }

    /**
     * Parse int enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    public function parseIntEnum(string $key, string $enum): BackedEnum
    {
        return $this->parseNullableIntEnum($key, $enum) ?? $enum::cases()[0];
    }

    /**
     * Parse nullable int enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    public function parseNullableIntEnum(string $key, string $enum): ?BackedEnum
    {
        $value = $this->parseNullableInt($key);

        if ($value === null) {
            return $value;
        }

        return $enum::tryFrom($value);
    }

    /**
     * Must parse int enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    public function mustParseIntEnum(string $key, string $enum): BackedEnum
    {
        $value = $this->mustParseNullableIntEnum($key, $enum);

        \assert($value !== null, \sprintf('value:[%s] is not class:[%s] int enum on:[%s]', \get_debug_type($value), $enum, static::class));

        return $value;
    }

    /**
     * Must parse nullable int enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    public function mustParseNullableIntEnum(string $key, string $enum): ?BackedEnum
    {
        $value = $this->mustParseNullableInt($key);

        if ($value === null) {
            return $value;
        }

        return $enum::from($value);
    }

    /**
     * Parse string enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    public function parseStringEnum(string $key, string $enum): BackedEnum
    {
        return $this->parseNullableStringEnum($key, $enum) ?? $enum::cases()[0];
    }

    /**
     * Parse nullable string enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    public function parseNullableStringEnum(string $key, string $enum): ?BackedEnum
    {
        $value = $this->parseNullableString($key);

        if ($value === null) {
            return $value;
        }

        return $enum::tryFrom($value);
    }

    /**
     * Must parse string enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    public function mustParseStringEnum(string $key, string $enum): BackedEnum
    {
        $value = $this->mustParseNullableStringEnum($key, $enum);

        \assert($value !== null, \sprintf('value:[%s] is not class:[%s] string enum on [%s]', \get_debug_type($value), $enum, static::class));

        return $value;
    }

    /**
     * Must parse nullable string enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    public function mustParseNullableStringEnum(string $key, string $enum): ?BackedEnum
    {
        $value = $this->mustParseNullableString($key);

        if ($value === null) {
            return $value;
        }

        return $enum::from($value);
    }
}
