<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class MeUpdateRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response|bool
    {
        $this->resolveMe();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        $authValidity = inject(AuthValidity::class);

        $guardName = $this->guardName();

        return [
            'email' => $authValidity->email($guardName)->nullable()->filled(),
            'name' => $authValidity->name($guardName)->nullable()->filled(),
            'locale' => $authValidity->locale($guardName)->nullable()->filled(),
        ];
    }

    /**
     * Get guard name.
     */
    public function guardName(): string
    {
        return resolveAuthManager()->getDefaultDriver();
    }

    /**
     * Get credentials.
     *
     * @return array<int, array<string, mixed>>
     */
    public function credentials(): array
    {
        return [$this->validatedInput()->only(['email'])];
    }

    /**
     * Get data.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->validatedInput()->all();
    }

    /**
     * Resolve me.
     */
    public function resolveMe(): AuthenticatableContract
    {
        return once(fn (): AuthenticatableContract => mustResolveUser([$this->guardName()]));
    }
}
