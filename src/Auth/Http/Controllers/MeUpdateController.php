<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\MeUpdateRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Routing\TransactionController;

class MeUpdateController extends TransactionController
{
    /**
     * Throttle max attempts.
     */
    public static int $throttle = 5;

    /**
     * Throttle decay in minutes.
     */
    public static int $decay = 15;

    /**
     * Throw simple throttle errors.
     */
    public static bool $simpleThrottle = false;

    /**
     * Handle the incoming request.
     */
    public function __invoke(MeUpdateRequest $request): SymfonyResponse
    {
        $this->validateUnique($request);

        $response = $this->beforeUpdating($request);

        if ($response !== null) {
            return $response;
        }

        $this->updateUser($request);

        return $this->response($request);
    }

    /**
     * Throttle limit.
     */
    protected function limit(MeUpdateRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->hash());
    }

    /**
     * Throttle callback.
     *
     * @param array<string, mixed> $credentials
     *
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(MeUpdateRequest $request, array $credentials): ?Closure
    {
        return function (int $seconds) use ($credentials): never {
            if (static::$simpleThrottle) {
                throw new ThrottleRequestsException();
            }

            $this->throwThrottleValidationError(\array_keys($credentials), $seconds);
        };
    }

    /**
     * Retrieve by credentials.
     *
     * @param array<string, mixed> $credentials
     */
    protected function retrieveByCredentials(MeUpdateRequest $request, array $credentials): ?AuthenticatableContract
    {
        return $this->userProvider($request)->retrieveByCredentials($credentials);
    }

    /**
     * Get user provider.
     */
    protected function userProvider(MeUpdateRequest $request): UserProviderContract
    {
        return inject(AuthService::class)->userProvider(resolveAuthManager()->guard($request->guardName()));
    }

    /**
     * Make response.
     */
    protected function response(MeUpdateRequest $request): SymfonyResponse
    {
        return inject(AuthService::class)->jsonApiResource($request->retrieveUser())->toResponse($request);
    }

    /**
     * Throw duplicate credentials error.
     *
     * @param array<string, mixed> $credentials
     */
    protected function throwDuplicateCredentialsError(MeUpdateRequest $request, array $credentials): never
    {
        $validator = resolveValidatorFactory()->make([], []);

        foreach ($credentials as $key => $value) {
            $validator->addFailure($key, 'unique');
        }

        throw new ValidationException($validator);
    }

    /**
     * Before updating shortcut.
     */
    protected function beforeUpdating(MeUpdateRequest $request): ?SymfonyResponse
    {
        return null;
    }

    /**
     * Validate given credentials are unique.
     */
    protected function validateUnique(MeUpdateRequest $request): void
    {
        $credentialsArray = $request->credentials();

        $me = $request->retrieveUser();

        foreach ($credentialsArray as $index => $credentials) {
            [$hit] = $this->throttle($this->limit($request, "credentials.{$index}"), $this->onThrottle($request, $credentials));

            $user = $this->retrieveByCredentials($request, $credentials);

            if ($user !== null && $user->getAuthIdentifier() !== $me->getAuthIdentifier()) {
                $hit();

                $this->throwDuplicateCredentialsError($request, $credentials);
            }
        }
    }

    /**
     * Update user.
     */
    protected function updateUser(MeUpdateRequest $request): void
    {
        $user = $request->retrieveUser();

        \assert($user instanceof Model);

        $user->forceFill($request->data());

        $this->makeChanges($request, $user);

        if (! $user->isDirty()) {
            return;
        }

        $ok = $user->save();

        \assert($ok);

        $user->refresh();
    }

    /**
     * Make changes.
     */
    protected function makeChanges(MeUpdateRequest $request, Model&AuthenticatableContract $user): void
    {
    }
}
