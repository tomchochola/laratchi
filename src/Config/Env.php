<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Config;

use Illuminate\Support\Env as IlluminateEnv;
use RuntimeException;
use Tomchochola\Laratchi\Support\AssertTrait;
use Tomchochola\Laratchi\Support\ParseTrait;
use Tomchochola\Laratchi\Support\Resolver;

class Env
{
    use AssertTrait;
    use ParseTrait;

    /**
     * Mixed getter.
     */
    public function mixed(?string $key = null): mixed
    {
        if (Resolver::resolveApp()->bound('env')) {
            throw new RuntimeException('env is already bound to the container');
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
        return $this->assertString('APP_ENV');
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
