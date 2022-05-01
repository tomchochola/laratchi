<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tomchochola\Laratchi\Validation\SecureValidator;

class NonEmptySecureRequest extends SecureFormRequest
{
    /**
     * HTTP Exception message.
     */
    final public const ERROR_MESSAGE = 'Request Can Not Be Empty';

    /**
     * HTTP Exception status.
     */
    final public const ERROR_STATUS = SymfonyResponse::HTTP_BAD_REQUEST;

    /**
     * @inheritDoc
     */
    protected function passedValidation(): void
    {
        parent::passedValidation();

        $this->validateRequestIsNotEmpty();
    }

    /**
     * Validate that request is not empty.
     */
    protected function validateRequestIsNotEmpty(): void
    {
        if (\count($this->except(SecureValidator::$excluded)) === 0) {
            throw new HttpException(static::ERROR_STATUS, static::ERROR_MESSAGE);
        }
    }
}
