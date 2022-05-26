<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Services;

use Illuminate\Auth\RequestGuard;
use Illuminate\Auth\SessionGuard;
use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Database\Eloquent\Model;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;
use Tomchochola\Laratchi\Auth\Http\Resources\MeJsonApiResource;
use Tomchochola\Laratchi\Http\Resources\JsonApiResource;

class AuthService
{
    /**
     * Default me json api resource.
     *
     * @var class-string<JsonApiResource>
     */
    public static string $jsonApiResource = MeJsonApiResource::class;

    /**
     * Resolve user provider from guard.
     */
    public function userProvider(GuardContract $guard): UserProviderContract
    {
        \assert($guard instanceof SessionGuard || $guard instanceof RequestGuard || $guard instanceof TokenGuard || $guard instanceof DatabaseTokenGuard);

        return $guard->getProvider();
    }

    /**
     * Resolve json api resource from user.
     */
    public function jsonApiResource(AuthenticatableContract $user): JsonApiResource
    {
        \assert($user instanceof Model);

        return new static::$jsonApiResource($user);
    }
}
