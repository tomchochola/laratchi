<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Config;

use Illuminate\Config\Repository;
use RuntimeException;
use Tomchochola\Laratchi\Support\AssertTrait;
use Tomchochola\Laratchi\Support\AssignTrait;
use Tomchochola\Laratchi\Support\ParserTrait;
use Tomchochola\Laratchi\Support\Resolver;

class Config
{
    use AssertTrait;
    use AssignTrait;
    use ParserTrait;

    /**
     * Config repository.
     */
    public Repository $repository;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->repository = Resolver::resolveConfigRepository();
    }

    /**
     * Mixed getter.
     */
    public function mixed(?string $key = null): mixed
    {
        if (! Resolver::resolveApp()->bound('env')) {
            throw new RuntimeException('env is not bound to the container');
        }

        if ($key === null) {
            return $this->repository->all();
        }

        return $this->repository->get($key);
    }

    /**
     * Mixed setter.
     *
     * @return $this
     */
    public function assign(string $key, mixed $value): static
    {
        if (! Resolver::resolveApp()->bound('env')) {
            throw new RuntimeException('env is not bound to the container');
        }

        $this->repository->set($key, $value);

        return $this;
    }

    /**
     * App locale getter.
     */
    public function appLocale(): string
    {
        return $this->assertString('app.locale');
    }

    /**
     * App locales getter.
     *
     * @return array<int, string>
     */
    public function appLocales(): array
    {
        $locales = [];

        foreach ($this->assertArray('app.locales') as $locale) {
            $locales[] = assertString($locale);
        }

        return $locales;
    }

    /**
     * App name getter.
     */
    public function appName(): string
    {
        return $this->assertString('app.name');
    }

    /**
     * App debug getter.
     */
    public function appDebug(): bool
    {
        return $this->assertBool('app.debug');
    }

    /**
     * Auth guards getter.
     *
     * @return array<int, string>
     */
    public function authGuards(): array
    {
        $guards = [];

        foreach (\array_keys($this->assertArray('auth.guards')) as $guard) {
            $guards[] = assertString($guard);
        }

        return $guards;
    }

    /**
     * App env getter.
     */
    public function appEnv(): string
    {
        return $this->assertString('app.env');
    }

    /**
     * App env is.
     *
     * @param array<string> $envs
     */
    public function appEnvIs(array $envs): bool
    {
        return \in_array($this->appEnv(), $envs, true);
    }

    /**
     * Map app env.
     *
     * @template T
     *
     * @param array<string, T> $mapping
     *
     * @return T
     */
    public function appEnvMap(array $mapping): mixed
    {
        return $mapping[$this->appEnv()];
    }

    /**
     * App timezone getter.
     */
    public function appTimezone(): string
    {
        return $this->assertString('app.timezone');
    }
}
