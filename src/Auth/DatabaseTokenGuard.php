<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Support\Str;
use Tomchochola\Laratchi\Auth\Services\CanLoginService;

class DatabaseTokenGuard implements GuardContract
{
    /**
     * The currently authenticated user.
     */
    public User|false|null $user = null;

    /**
     * The currently authenticated database token.
     */
    public ?DatabaseToken $databaseToken = null;

    /**
     * Create a new guard instance.
     */
    public function __construct(public string $guardName)
    {
    }

    /**
     * Get cookie name.
     */
    public function cookieName(): string
    {
        $env = currentEnv();

        return ($env === 'local' ? '' : '__Host-').Str::slug(mustConfigString('app.name').'_'.$env."_database_token_{$this->guardName}", '_');
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
    public function user(): ?User
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

        $databaseToken = DatabaseToken::inject()->findByBearer($bearer);

        if ($databaseToken === null) {
            return $this->user = $this->databaseToken = null;
        }

        $user = $databaseToken->user($this->guardName);

        if ($user === null || CanLoginService::inject()->authorize($user)->denied()) {
            return $this->user = $this->databaseToken = null;
        }

        $this->databaseToken = $databaseToken;
        $this->user = $user;

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function id(): ?int
    {
        return $this->user()?->getKey();
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $credentials
     */
    public function validate(array $credentials = []): never
    {
        assertNever();
    }

    /**
     * @inheritDoc
     */
    public function hasUser(): bool
    {
        return $this->user instanceof User;
    }

    /**
     * @inheritDoc
     */
    public function setUser(AuthenticatableContract $user): void
    {
        \assert($user instanceof User);

        $this->user = $user;
    }

    /**
     * Log a user into the application.
     */
    public function login(User $user): DatabaseToken
    {
        $databaseToken = DatabaseToken::inject()->login($this->guardName, $user);

        $this->databaseToken = $databaseToken;
        $this->user = $user;

        $bearer = $databaseToken->bearer;

        \assert($bearer !== null);

        $cookieJar = resolveCookieJar();
        $cookieJar->queue($cookieJar->forever($this->cookieName(), $bearer, '/', null, true, true, false, resolveApp()->isProduction() ? 'Lax' : 'None'));

        return $databaseToken;
    }

    /**
     * Logout.
     */
    public function logout(): void
    {
        if ($this->databaseToken !== null) {
            $this->databaseToken->newQuery()->whereKey($this->databaseToken->getKey())->delete();
        }

        $this->databaseToken = null;
        $this->user = false;

        resolveCookieJar()->expire($this->cookieName(), '/', null);
    }

    /**
     * Resolve bearer from request.
     */
    public function bearer(): ?string
    {
        $request = resolveRequest();

        $bearer = $request->bearerToken();

        if ($bearer === null) {
            $bearer = $request->cookies->get($this->cookieName());
        }

        if (\is_string($bearer)) {
            return $bearer;
        }

        return null;
    }
}
