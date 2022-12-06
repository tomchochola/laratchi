<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class MeShowRequest extends SecureFormRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        $authValidity = inject(AuthValidity::class);

        return \array_merge(parent::rules(), [
            'guard' => $authValidity->guard()->nullable()->filled(),
        ]);
    }

    /**
     * Get guard name.
     */
    public function guardName(): string
    {
        if ($this->has('guard')) {
            return $this->str('guard')->value();
        }

        return resolveAuthManager()->getDefaultDriver();
    }

    /**
     * Retrieve user.
     */
    public function retrieveUser(): ?AuthenticatableContract
    {
        return once(function (): ?AuthenticatableContract {
            return resolveUser([$this->guardName()]);
        });
    }
}
