<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class RequestSignature
{
    /**
     * Data.
     *
     * @var array<string, string>
     */
    public array $data = [];

    /**
     * Constructor.
     */
    public function __construct(public Request $request)
    {
    }

    /**
     * Default setup.
     */
    public function defaults(): static
    {
        return $this->auth()
            ->ip()
            ->action();
    }

    /**
     * Add auth to request signature.
     *
     * @return $this
     */
    public function auth(?string $guard = null): static
    {
        return $this->user(assertNullableInstance($this->request->user($guard), AuthenticatableContract::class));
    }

    /**
     * Add user to request signature.
     *
     * @return $this
     */
    public function user(?AuthenticatableContract $auth): static
    {
        if ($auth === null) {
            return $this->data('user', '');
        }

        return $this->data('user', \sprintf('%s:%s', $auth::class, assertNullableScalar($auth->getAuthIdentifier())));
    }

    /**
     * Add ip to request signature.
     *
     * @return $this
     */
    public function ip(): static
    {
        return $this->data('ip', $this->request->ip() ?? '');
    }

    /**
     * Add action to request signature.
     *
     * @return $this
     */
    public function action(): static
    {
        return $this->data('action', assertInstance($this->request->route(), Route::class)->getActionName());
    }

    /**
     * Add locale to request signature.
     *
     * @return $this
     */
    public function locale(): static
    {
        return $this->data('locale', $this->request->getLocale());
    }

    /**
     * Add query to request signature.
     *
     * @return $this
     */
    public function query(): static
    {
        return $this->data('query', $this->request->getQueryString() ?? '');
    }

    /**
     * Add path to request signature.
     *
     * @return $this
     */
    public function path(): static
    {
        return $this->data('path', $this->request->path());
    }

    /**
     * Add key to request signature.
     *
     * @return $this
     */
    public function key(string $key): static
    {
        return $this->data('key', $key);
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
        return \hash('sha256', \implode('|', $this->data));
    }
}
