<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class MustBeGuestHttpException extends HttpException
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(427, 'Must Be Guest');
    }
}
