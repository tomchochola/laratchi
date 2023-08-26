<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Illuminate\Validation\Validator;
use Throwable;
use Tomchochola\Laratchi\Interfaces\GetDataInterface;

class ValidationException extends IlluminateValidationException implements GetDataInterface
{
    /**
     * @inheritDoc
     *
     * @param array<mixed> $headers
     * @param array<mixed> $data
     *
     * @phpstan-ignore-next-line
     */
    public function __construct(
        Validator $validator,
        int $statusCode = 422,
        string $message = '',
        ?Throwable $previous = null,
        public array $headers = [],
        int $code = 0,
        public array $data = [],
    ) {
        $this->validator = $validator;
        $this->response = null;
        $this->status = $statusCode;
        $this->errorBag = '';

        Exception::__construct($message, $code, $previous);
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        return $this->data;
    }
}
