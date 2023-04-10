<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Services;

use Illuminate\Auth\Access\Response;
use Tomchochola\Laratchi\Auth\User;

class CanLoginService
{
    /**
     * Template.
     *
     * @var class-string<self>
     */
    public static string $template = self::class;

    /**
     * Inject.
     */
    public static function inject(): self
    {
        return new static::$template();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(User $user): Response
    {
        return Response::allow();
    }
}
