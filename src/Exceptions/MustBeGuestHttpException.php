<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class MustBeGuestHttpException extends HttpException
{
    /**
     * HTTP Exception message.
     */
    final public const ERROR_MESSAGE = 'Must Be Guest';

    /**
     * HTTP Exception status.
     */
    final public const ERROR_STATUS = 427;

    /**
     * @inheritDoc
     *
     * @param array<string, mixed> $headers
     */
    public function __construct(?Throwable $previous = null, array $headers = [], int $code = 0)
    {
        parent::__construct(static::ERROR_STATUS, static::ERROR_MESSAGE, $previous, $headers, $code);
    }
}
