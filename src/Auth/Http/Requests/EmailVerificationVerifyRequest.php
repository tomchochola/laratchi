<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Http\Requests\SignedRequest;

class EmailVerificationVerifyRequest extends SignedRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        $authValidity = inject(AuthValidity::class);

        $guardName = $this->guardName();

        return \array_replace(parent::rules(), [
            'guard' => $authValidity->guard()->nullable()->filled(),
            'email' => $authValidity->email($guardName)->required(),
            'id' => $authValidity->id($guardName)->required(),
        ]);
    }

    /**
     * Get guard name.
     */
    public function guardName(): string
    {
        if ($this->filled('guard')) {
            $guardName = $this->varchar('guard');

            if (\in_array($guardName, \array_keys(mustConfigArray('auth.guards')), true)) {
                return $guardName;
            }
        }

        return resolveAuthManager()->getDefaultDriver();
    }

    /**
     * Get credentials.
     *
     * @return array<string, mixed>
     */
    public function credentials(): array
    {
        return $this->validatedInput()->only(['id', 'email']);
    }
}
