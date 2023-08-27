<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Exceptions;

use RuntimeException;
use Throwable;

class Panicker
{
    /**
     * Panic.
     *
     * @param array<mixed> $args
     */
    public static function panic(string $source, string $message = 'panic', array $args = [], int $code = 0, ?Throwable $previous = null): never
    {
        throw new RuntimeException(static::message($source, $message, $args), $code, $previous);
    }

    /**
     * Message.
     *
     * @param array<mixed> $args
     */
    public static function message(string $source, string $message, array $args): string
    {
        $msg = "[{$source}] - {$message}";

        if (\count($args) === 0) {
            return $msg;
        }

        return "{$msg} | ".static::args($args);
    }

    /**
     * Encode args to string.
     *
     * @param array<mixed> $args
     */
    public static function args(array $args): string
    {
        $encoded = [];

        foreach ($args as $key => $value) {
            $encoded[] = $key.'('.\get_debug_type($value).'):'.static::arg($value);
        }

        return \implode(' ', $encoded);
    }

    /**
     * Encode arg to string.
     */
    public static function arg(mixed $value): string
    {
        if (\is_string($value)) {
            return '"'.\str_replace('"', '""', $value).'"';
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (\is_array($value)) {
            return 'array';
        }

        if (\is_object($value)) {
            return $value::class;
        }

        if (\is_callable($value)) {
            return 'callable';
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        if (\is_scalar($value)) {
            return (string) $value;
        }

        static::panic(__METHOD__);
    }
}
