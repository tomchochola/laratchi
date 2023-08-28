<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Config;

use Illuminate\Foundation\Application;
use Illuminate\Support\Env as IlluminateEnv;
use Tomchochola\Laratchi\Container\InjectTrait;
use Tomchochola\Laratchi\Exceptions\Panicker;
use Tomchochola\Laratchi\Support\AssertTrait;
use Tomchochola\Laratchi\Support\ParseTrait;
use Tomchochola\Laratchi\Support\Resolver;

class Env
{
    use AssertTrait;
    use InjectTrait;
    use ParseTrait;

    /**
     * App.
     */
    public Application $app;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->app = Resolver::resolveApp();
    }

    /**
     * Mixed getter.
     */
    public function mixed(?string $key = null): mixed
    {
        if ($this->app->bound('env')) {
            Panicker::panic(__METHOD__, 'env is already bound to the container');
        }

        $value = IlluminateEnv::get($key ?? '');

        if ($value === '') {
            return null;
        }

        return $value;
    }

    /**
     * App env getter.
     */
    public function appEnv(): string
    {
        return $this->mustParseString('APP_ENV');
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
     * App env map.
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
}
