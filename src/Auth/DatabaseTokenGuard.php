<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\CurrentDeviceLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Support\Str;
use LogicException;

class DatabaseTokenGuard implements GuardContract
{
    /**
     * Cookie name.
     */
    public static string $cookieName = 'database_token';

    /**
     * The currently authenticated user.
     */
    public DatabaseTokenableInterface|false|null $user = null;

    /**
     * The currently authenticated database token.
     */
    public ?DatabaseToken $databaseToken = null;

    /**
     * Create a new guard instance.
     */
    public function __construct(public string $guardName, public string $userProviderName)
    {
    }

    /**
     * Get cookie name.
     */
    public function cookieName(): string
    {
        return Str::slug(mustConfigString('session.cookie').'_'.static::$cookieName."_{$this->guardName}", '_');
    }

    /**
     * @inheritDoc
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @inheritDoc
     */
    public function guest(): bool
    {
        return $this->check() === false;
    }

    /**
     * @inheritDoc
     */
    public function user(): ?DatabaseTokenableInterface
    {
        if ($this->user === false) {
            return null;
        }

        if ($this->user !== null) {
            return $this->user;
        }

        $bearer = $this->bearer();

        if ($bearer === null) {
            return $this->user = $this->databaseToken = null;
        }

        $databaseToken = inject(DatabaseToken::class)->find($bearer);

        if ($databaseToken === null) {
            return $this->user = $this->databaseToken = null;
        }

        $user = $databaseToken->user();

        if ($user === null) {
            return $this->user = $this->databaseToken = null;
        }

        $user->setDatabaseToken($databaseToken);

        $this->databaseToken = $databaseToken;
        $this->setUser($user);

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function id(): int|string|null
    {
        $user = $this->user();

        if ($user === null) {
            return null;
        }

        $id = $user->getAuthIdentifier();

        \assert(\is_int($id) || \is_string($id));

        return $id;
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $credentials
     */
    public function validate(array $credentials = []): never
    {
        throw new LogicException('Not implemented.');
    }

    /**
     * @inheritDoc
     */
    public function hasUser(): bool
    {
        return $this->user instanceof DatabaseTokenableInterface;
    }

    /**
     * @inheritDoc
     */
    public function setUser(AuthenticatableContract $user, bool $events = true): void
    {
        \assert($user instanceof DatabaseTokenableInterface);
        \assert($user->getUserProviderName() === $this->userProviderName);

        $this->user = $user;

        if ($events) {
            resolveEventDispatcher()->dispatch(new Authenticated($this->guardName, $user));
        }
    }

    /**
     * Log a user into the application.
     */
    public function login(DatabaseTokenableInterface $user, bool $remember = false, bool $events = true): DatabaseToken
    {
        $databaseToken = $this->createToken($user);

        $user->setDatabaseToken($databaseToken);

        $this->queueCookie($databaseToken);

        if ($events) {
            resolveEventDispatcher()->dispatch(new Login($this->guardName, $user, false));
        }

        $this->databaseToken = $databaseToken;
        $this->setUser($user, $events);

        return $databaseToken;
    }

    /**
     * Queue authorization cookie.
     */
    public function queueCookie(DatabaseToken $databaseToken): void
    {
        $cookieJar = resolveCookieJar();

        $cookieJar->queue($cookieJar->forever($this->cookieName(), $databaseToken->bearer));
    }

    /**
     * Create a new database token.
     */
    public function createToken(DatabaseTokenableInterface $user): DatabaseToken
    {
        \assert($user->getUserProviderName() === $this->userProviderName);

        return inject(DatabaseToken::class)->create($user);
    }

    /**
     * Log the user out of the application on their current device only.
     */
    public function logoutCurrentDevice(bool $events = true): void
    {
        $user = $this->user();

        \assert($user !== null);

        $this->databaseToken?->delete();

        $user->setDatabaseToken(null);

        if ($events) {
            resolveEventDispatcher()->dispatch(new CurrentDeviceLogout($this->guardName, $user));
        }

        $this->databaseToken = null;
        $this->user = false;
    }

    /**
     * Get the user provider used by the guard.
     */
    public function getProvider(): UserProviderContract
    {
        $userProvider = resolveAuthManager()->createUserProvider($this->userProviderName);

        \assert($userProvider !== null);

        return $userProvider;
    }

    /**
     * Resolve bearer from request.
     */
    public function bearer(): ?string
    {
        $request = resolveRequest();

        $bearer = $request->bearerToken();

        if ($bearer === null) {
            $bearer = $request->cookie($this->cookieName());
        }

        if (\is_array($bearer)) {
            $bearer = \end($bearer);
        }

        if (blank($bearer)) {
            return null;
        }

        return $bearer;
    }
}
