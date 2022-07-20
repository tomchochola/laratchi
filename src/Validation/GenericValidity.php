<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

class GenericValidity
{
    /**
     * Signature validation rules.
     */
    public function signature(): Validity
    {
        return Validity::make()->char(64);
    }

    /**
     * Expires validation rules.
     */
    public function expires(): Validity
    {
        return Validity::make()->unsignedBigInt(1);
    }

    /**
     * ID validation rules.
     */
    public function id(): Validity
    {
        return Validity::make()->unsignedBigInt(1);
    }

    /**
     * Slug validation rules.
     */
    public function slug(): Validity
    {
        return Validity::make()->defaultString();
    }

    /**
     * HTTP method validation rules.
     */
    public function method(): Validity
    {
        return Validity::make()->string()->in(['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE']);
    }

    /**
     * Locale validation rules.
     */
    public function locale(): Validity
    {
        return Validity::make()->string()->in(mustConfigArray('app.locales', [resolveApp()->getLocale()]));
    }

    /**
     * Mark attribute as unvalidated.
     */
    public function unvalidated(): Validity
    {
        return Validity::make();
    }

    /**
     * Generic validation rules.
     */
    public function generic(): Validity
    {
        return Validity::make();
    }

    /**
     * Filter validation rules.
     */
    public function filter(): Validity
    {
        return $this->object();
    }

    /**
     * Object validation rules.
     */
    public function object(): Validity
    {
        return Validity::make()->object();
    }

    /**
     * Collection validation rules.
     */
    public function collection(int $minItems, ?int $maxItems = null): Validity
    {
        return Validity::make()->collection($minItems, $maxItems);
    }
}
