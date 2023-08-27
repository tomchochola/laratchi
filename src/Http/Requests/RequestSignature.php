<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Stringable;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Support\Resolver;

class RequestSignature implements Stringable
{
    /**
     * Request signature data.
     *
     * @var array<string, string>
     */
    public array $data = [];

    /**
     * Internal signature data.
     *
     * @var array<string, string>
     */
    protected array $internal = [];

    /**
     * Create a new request signature instance.
     */
    public function __construct(string $key, bool $defaults = true)
    {
        $this->key($key);

        if ($defaults) {
            $this->auth();
            $this->ip();
            $this->action();
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
     * Add auth to request signature.
     *
     * @return $this
     */
    public function auth(): static
    {
        return $this->user(User::auth());
    }

    /**
     * Add user to request signature.
     *
     * @return $this
     */
    public function user(?User $user): static
    {
        if ($user === null) {
            $this->internal['user'] = '';
        } else {
            $this->internal['user'] = "{$user->getTable()}:{$user->getKey()}";
        }

        return $this;
    }

    /**
     * Add ip to request signature.
     *
     * @return $this
     */
    public function ip(): static
    {
        $this->internal['ip'] = resolveRequest()->ip() ?? '';

        return $this;
    }

    /**
     * Add action to request signature.
     *
     * @return $this
     */
    public function action(): static
    {
        $this->internal['action'] = Resolver::resolveRoute()->getActionName();

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
     * Add query to request signature.
     *
     * @return $this
     */
    public function query(): static
    {
        $this->internal['query'] = resolveRequest()->getQueryString() ?? '';

        return $this;
    }

    /**
     * Add key to request signature.
     *
     * @return $this
     */
    public function key(string $key): static
    {
        $this->internal['key'] = $key;

        return $this;
    }

    /**
     * Add data to request signature.
     *
     * @return $this
     */
    public function data(string $key, string $data): static
    {
        $this->data[$key] = $data;

        return $this;
    }

    /**
     * Make request signature hash.
     */
    public function hash(): string
    {
        return \hash('sha256', \implode('|', \array_replace($this->internal, $this->data)));
    }
}
