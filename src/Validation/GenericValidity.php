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
        return Validity::make()->string();
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
        return Validity::make()->string();
    }

    /**
     * HTTP method validation rules.
     */
    public function method(): Validity
    {
        return Validity::make()->string(7)->in(['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE']);
    }

    /**
     * Locale validation rules.
     */
    public function locale(): Validity
    {
        return Validity::make()->char(2)->in(mustConfigArray('app.locales'));
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
        return Validity::make()->object();
    }
}
