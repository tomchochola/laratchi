<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Stringable;

class RequestSignature implements Stringable
{
    /**
     * Request signature data.
     *
     * @var array<mixed>
     */
    public array $data = [];

    /**
     * Internal signature data.
     *
     * @var array<mixed>
     */
    protected array $internal = [];

    /**
     * Create a new request signature instance.
     *
     * @param array<mixed> $data
     */
    public function __construct(array $data = [], bool $defaults = true)
    {
        $this->data = $data;

        if ($defaults) {
            $this->ip();
            $this->url();
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->hash();
    }

    /**
     * Add user to request signature.
     *
     * @return $this
     */
    public function user(AuthenticatableContract $user): static
    {
        $this->internal['user'] = $user->getAuthIdentifier().':'.$user::class;

        return $this;
    }

    /**
     * Add ip to request signature.
     *
     * @return $this
     */
    public function ip(): static
    {
        $this->internal['ip'] = resolveRequest()->ip();

        return $this;
    }

    /**
     * Add url to request signature.
     *
     * @return $this
     */
    public function url(): static
    {
        $this->internal['url'] = resolveRequest()->getMethod().':'.resolveUrlFactory()->current();

        return $this;
    }

    /**
     * Add locale to request signature.
     *
     * @return $this
     */
    public function locale(): static
    {
        $this->internal['locale'] = resolveApp()->getLocale();

        return $this;
    }

    /**
     * Add route name to request signature.
     *
     * @return $this
     */
    public function route(): static
    {
        $this->internal['route'] = resolveRouter()->current()?->getName();

        return $this;
    }

    /**
     * Add query to request signature.
     *
     * @return $this
     */
    public function query(): static
    {
        $query = resolveRequest()->query();

        \assert(\is_array($query));

        \ksort($query);

        $this->internal['query'] = $query;

        return $this;
    }

    /**
     * Add data to request signature.
     *
     * @return $this
     */
    public function data(string $key, mixed $data): static
    {
        $this->data[$key] = $data;

        return $this;
    }

    /**
     * Make request signature hash.
     */
    public function hash(): string
    {
        return \hash('sha256', \serialize([$this->data, $this->internal]));
    }
}
