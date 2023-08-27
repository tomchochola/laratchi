<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use BackedEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use ReflectionEnum;
use stdClass;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Exceptions\Panicker;

class Typer
{
    /**
     * Assert string.
     */
    public static function assertString(mixed $value): string
    {
        \assert(\is_string($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert nullable string.
     */
    public static function assertNullableString(mixed $value): ?string
    {
        \assert($value === null || \is_string($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert bool.
     */
    public static function assertBool(mixed $value): bool
    {
        \assert(\is_bool($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert nullable bool.
     */
    public static function assertNullableBool(mixed $value): ?bool
    {
        \assert($value === null || \is_bool($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert int.
     */
    public static function assertInt(mixed $value): int
    {
        \assert(\is_int($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert nullable int.
     */
    public static function assertNullableInt(mixed $value): ?int
    {
        \assert($value === null || \is_int($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert float.
     */
    public static function assertFloat(mixed $value): float
    {
        \assert(\is_float($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert nullable float.
     */
    public static function assertNullableFloat(mixed $value): ?float
    {
        \assert($value === null || \is_float($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert array.
     *
     * @return array<mixed>
     */
    public static function assertArray(mixed $value): array
    {
        \assert(\is_array($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert nullable array.
     *
     * @return array<mixed>|null
     */
    public static function assertNullableArray(mixed $value): ?array
    {
        \assert($value === null || \is_array($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert file.
     */
    public static function assertFile(mixed $value): UploadedFile
    {
        \assert($value instanceof UploadedFile, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert nullable file.
     */
    public static function assertNullableFile(mixed $value): ?UploadedFile
    {
        \assert($value === null || $value instanceof UploadedFile, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert carbon.
     */
    public static function assertCarbon(mixed $value): Carbon
    {
        \assert($value instanceof Carbon, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert nullable carbon.
     */
    public static function assertNullableCarbon(mixed $value): ?Carbon
    {
        \assert($value === null || $value instanceof Carbon, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert object.
     */
    public static function assertObject(mixed $value): object
    {
        \assert(\is_object($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert nullable object.
     */
    public static function assertNullableObject(mixed $value): ?object
    {
        \assert($value === null || \is_object($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert scalar.
     *
     * @return scalar
     */
    public static function assertScalar(mixed $value): int|float|string|bool
    {
        \assert(\is_scalar($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert nullable scalar.
     *
     * @return scalar|null
     */
    public static function assertNullableScalar(mixed $value): int|float|string|bool|null
    {
        \assert($value === null || \is_scalar($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

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
    public static function assertEnum(mixed $value, string $enum): BackedEnum
    {
        if ($value instanceof $enum) {
            return $value;
        }

        Panicker::panic(__METHOD__, 'assertion failed', \compact('value', 'enum'));
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
    public static function assertNullableEnum(mixed $value, string $enum): ?BackedEnum
    {
        if ($value === null || $value instanceof $enum) {
            return $value;
        }

        Panicker::panic(__METHOD__, 'assertion failed', \compact('value', 'enum'));
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
    public static function assertInstance(mixed $value, string $class): object
    {
        if ($value instanceof $class) {
            return $value;
        }

        Panicker::panic(__METHOD__, 'assertion failed', \compact('value', 'class'));
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
    public static function assertNullableInstance(mixed $value, string $class): ?object
    {
        if ($value === null || $value instanceof $class) {
            return $value;
        }

        Panicker::panic(__METHOD__, 'assertion failed', \compact('value', 'class'));
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
    public static function assertA(mixed $value, string $class): string
    {
        if (\is_string($value) && \is_a($value, $class, true)) {
            return $value;
        }

        Panicker::panic(__METHOD__, 'assertion failed', \compact('value', 'class'));
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
    public static function assertNullableA(mixed $value, string $class): ?string
    {
        if ($value === null) {
            return $value;
        }

        if (\is_string($value) && \is_a($value, $class, true)) {
            return $value;
        }

        Panicker::panic(__METHOD__, 'assertion failed', \compact('value', 'class'));
    }

    /**
     * Parse string.
     */
    public static function parseString(mixed $value): string
    {
        return parseNullableString($value) ?? '';
    }

    /**
     * Parse nullable string.
     */
    public static function parseNullableString(mixed $value): ?string
    {
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
    public static function mustParseString(mixed $value): string
    {
        $value = mustParseNullableString($value);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Must parse nullable string.
     */
    public static function mustParseNullableString(mixed $value): ?string
    {
        if ($value === null || \is_string($value)) {
            return $value;
        }

        $value = \filter_var($value);

        \assert($value !== false, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Parse bool.
     */
    public static function parseBool(mixed $value): bool
    {
        return parseNullableBool($value) ?? false;
    }

    /**
     * Parse nullable bool.
     */
    public static function parseNullableBool(mixed $value): ?bool
    {
        if ($value === null || \is_bool($value)) {
            return $value;
        }

        return \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);
    }

    /**
     * Must parse bool.
     */
    public static function mustParseBool(mixed $value): bool
    {
        $value = mustParseNullableBool($value);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Must parse nullable bool.
     */
    public static function mustParseNullableBool(mixed $value): ?bool
    {
        if ($value === null || \is_bool($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Parse int.
     */
    public static function parseInt(mixed $value): int
    {
        return parseNullableInt($value) ?? 0;
    }

    /**
     * Parse nullable int.
     */
    public static function parseNullableInt(mixed $value): ?int
    {
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
    public static function mustParseInt(mixed $value): int
    {
        $value = mustParseNullableInt($value);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Must parse nullable int.
     */
    public static function mustParseNullableInt(mixed $value): ?int
    {
        if ($value === null || \is_int($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_INT);

        \assert($value !== false, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Parse float.
     */
    public static function parseFloat(mixed $value): float
    {
        return parseNullableFloat($value) ?? 0.0;
    }

    /**
     * Parse nullable float.
     */
    public static function parseNullableFloat(mixed $value): ?float
    {
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
    public static function mustParseFloat(mixed $value): float
    {
        $value = mustParseNullableFloat($value);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Must parse nullable float.
     */
    public static function mustParseNullableFloat(mixed $value): ?float
    {
        if ($value === null || \is_int($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_FLOAT);

        \assert($value !== false, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Parse array.
     *
     * @return array<mixed>
     */
    public static function parseArray(mixed $value): array
    {
        return parseNullableArray($value) ?? [];
    }

    /**
     * Parse nullable array.
     *
     * @return array<mixed>|null
     */
    public static function parseNullableArray(mixed $value): ?array
    {
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
    public static function mustParseArray(mixed $value): array
    {
        $value = mustParseNullableArray($value);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Must parse nullable array.
     *
     * @return array<mixed>|null
     */
    public static function mustParseNullableArray(mixed $value): ?array
    {
        if (\is_object($value)) {
            return \get_object_vars($value);
        }

        \assert($value === null || \is_array($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Parse file.
     */
    public static function parseFile(mixed $value): UploadedFile
    {
        return parseNullableFile($value) ?? UploadedFile::createFromBase(UploadedFile::fake()->create(assertString(\tempnam(\sys_get_temp_dir(), 'php'))));
    }

    /**
     * Parse nullable file.
     */
    public static function parseNullableFile(mixed $value): ?UploadedFile
    {
        if ($value === null || $value instanceof UploadedFile) {
            return $value;
        }

        return null;
    }

    /**
     * Must parse file.
     */
    public static function mustParseFile(mixed $value): UploadedFile
    {
        return assertFile($value);
    }

    /**
     * Must parse nullable file.
     */
    public static function mustParseNullableFile(mixed $value): ?UploadedFile
    {
        return assertNullableFile($value);
    }

    /**
     * Parse carbon.
     */
    public static function parseCarbon(mixed $value, ?string $format = null, ?string $tz = null): Carbon
    {
        return parseNullableCarbon($value, $format, $tz) ?? resolveNow();
    }

    /**
     * Parse nullable carbon.
     */
    public static function parseNullableCarbon(mixed $value, ?string $format = null, ?string $tz = null): ?Carbon
    {
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
                ->setTimezone(Config::inject()->appTimezone());
        }

        $value = resolveDate()->createFromFormat($format, $value, $tz);

        if ($value === false) {
            return null;
        }

        return $value->setTimezone(Config::inject()->appTimezone());
    }

    /**
     * Must parse carbon.
     */
    public static function mustParseCarbon(mixed $value, ?string $format = null, ?string $tz = null): Carbon
    {
        $value = mustParseNullableCarbon($value, $format, $tz);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Must parse nullable carbon.
     */
    public static function mustParseNullableCarbon(mixed $value, ?string $format = null, ?string $tz = null): ?Carbon
    {
        if ($value === null || $value instanceof Carbon) {
            return $value;
        }

        $value = \filter_var($value);

        \assert($value !== false && $value !== '', Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        if ($format === null) {
            return resolveDate()
                ->parse($value, $tz)
                ->setTimezone(Config::inject()->appTimezone());
        }

        $value = resolveDate()->createFromFormat($format, $value, $tz);

        \assert($value !== false, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value->setTimezone(Config::inject()->appTimezone());
    }

    /**
     * Parse object.
     */
    public static function parseObject(mixed $value): object
    {
        return parseNullableObject($value) ?? new stdClass();
    }

    /**
     * Parse nullable object.
     */
    public static function parseNullableObject(mixed $value): ?object
    {
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
    public static function mustParseObject(mixed $value): object
    {
        $value = mustParseNullableObject($value);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Must parse nullable object.
     */
    public static function mustParseNullableObject(mixed $value): ?object
    {
        if (\is_array($value)) {
            return (object) $value;
        }

        \assert($value === null || \is_object($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Parse scalar.
     */
    public static function parseScalar(mixed $value): string|int|float|bool
    {
        return parseNullableScalar($value) ?? '';
    }

    /**
     * Parse nullable scalar.
     */
    public static function parseNullableScalar(mixed $value): string|int|float|bool|null
    {
        if ($value === null || \is_scalar($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Must parse scalar.
     */
    public static function mustParseScalar(mixed $value): string|int|float|bool
    {
        $value = mustParseNullableScalar($value);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Must parse nullable scalar.
     */
    public static function mustParseNullableScalar(mixed $value): string|int|float|bool|null
    {
        \assert($value === null || \is_scalar($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

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
    public static function parseEnum(mixed $value, string $enum): BackedEnum
    {
        return parseNullableEnum($value, $enum) ?? $enum::cases()[0];
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
    public static function parseNullableEnum(mixed $value, string $enum): ?BackedEnum
    {
        if ((string) (new ReflectionEnum($enum))->getBackingType() === 'int') {
            $value = parseNullableInt($value);
        } else {
            $value = parseNullableString($value);
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
    public static function mustParseEnum(mixed $value, string $enum): BackedEnum
    {
        $value = mustParseNullableEnum($value, $enum);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

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
    public static function mustParseNullableEnum(mixed $value, string $enum): ?BackedEnum
    {
        if ((string) (new ReflectionEnum($enum))->getBackingType() === 'int') {
            $value = mustParseNullableInt($value);
        } else {
            $value = mustParseNullableString($value);
        }

        if ($value === null) {
            return $value;
        }

        return $enum::from($value);
    }

    /**
     * Assert in.
     *
     * @template T
     *
     * @param T $value
     * @param array<mixed> $enum
     *
     * @return T
     */
    public static function assertIn(mixed $value, array $enum): mixed
    {
        if (\in_array($value, $enum, true)) {
            return $value;
        }

        Panicker::panic(__METHOD__, 'assertion failed', \compact('value', 'enum'));
    }

    /**
     * Assert not null.
     *
     * @template T of string|int|float|bool|object|array
     *
     * @param T|null $value
     *
     * @return T
     */
    public static function assertNotNull(mixed $value): string|int|float|bool|object|array
    {
        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }

    /**
     * Assert null.
     */
    public static function assertNull(mixed $value): mixed
    {
        if ($value !== null) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('value'));
        }

        return $value;
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
    public static function parseIntEnum(mixed $value, string $enum): BackedEnum
    {
        return parseNullableIntEnum($value, $enum) ?? $enum::cases()[0];
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
    public static function parseNullableIntEnum(mixed $value, string $enum): ?BackedEnum
    {
        $value = parseNullableInt($value);

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
    public static function mustParseIntEnum(mixed $value, string $enum): BackedEnum
    {
        $value = mustParseNullableIntEnum($value, $enum);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value', 'enum')));

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
    public static function mustParseNullableIntEnum(mixed $value, string $enum): ?BackedEnum
    {
        $value = mustParseNullableInt($value);

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
    public static function parseStringEnum(mixed $value, string $enum): BackedEnum
    {
        return parseNullableStringEnum($value, $enum) ?? $enum::cases()[0];
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
    public static function parseNullableStringEnum(mixed $value, string $enum): ?BackedEnum
    {
        $value = parseNullableString($value);

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
    public static function mustParseStringEnum(mixed $value, string $enum): BackedEnum
    {
        $value = mustParseNullableStringEnum($value, $enum);

        \assert($value !== null, Panicker::message(__METHOD__, 'assertion failed', \compact('value', 'enum')));

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
    public static function mustParseNullableStringEnum(mixed $value, string $enum): ?BackedEnum
    {
        $value = mustParseNullableString($value);

        if ($value === null) {
            return $value;
        }

        return $enum::from($value);
    }

    /**
     * Assert.
     *
     * @param array<mixed> $args
     */
    public static function assert(bool $value, string $message = 'assertion failed', array $args = []): void
    {
        if ($value !== true) {
            Panicker::panic(__METHOD__, $message, $args);
        }
    }

    /**
     * Assert true.
     *
     * @return true
     */
    public static function assertTrue(mixed $value): bool
    {
        if ($value !== true) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('value'));
        }

        return true;
    }

    /**
     * Assert false.
     *
     * @return false
     */
    public static function assertFalse(mixed $value): bool
    {
        if ($value !== false) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('value'));
        }

        return false;
    }

    /**
     * Assert not true.
     *
     * @template T
     *
     * @param T $value
     *
     * @return T
     */
    public static function assertNotTrue(mixed $value): mixed
    {
        if ($value === true) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('value'));
        }

        return $value;
    }

    /**
     * Assert not false.
     *
     * @template T
     *
     * @param T $value
     *
     * @return T
     */
    public static function assertNotFalse(mixed $value): mixed
    {
        if ($value === false) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('value'));
        }

        return $value;
    }

    /**
     * Assert not empty.
     *
     * @template T of array<mixed>
     *
     * @param T $value
     *
     * @return T
     */
    public static function assertNotEmpty(array $value): array
    {
        if (\count($value) !== 0) {
            Panicker::panic(__METHOD__, 'assertion failed', \compact('value'));
        }

        return $value;
    }

    /**
     * Assert empty.
     *
     * @template T of array<mixed>
     *
     * @param T $value
     *
     * @return T
     */
    public static function assertEmpty(array $value): array
    {
        if (\count($value) === 0) {
            return $value;
        }

        Panicker::panic(__METHOD__, 'assertion failed', \compact('value'));
    }

    /**
     * Assert not bool.
     *
     * @template T of string|int|float|object|array|null
     *
     * @param T|bool $value
     *
     * @return T
     */
    public static function assertNotBool(mixed $value): string|int|float|object|array|null
    {
        \assert(! \is_bool($value), Panicker::message(__METHOD__, 'assertion failed', \compact('value')));

        return $value;
    }
}
