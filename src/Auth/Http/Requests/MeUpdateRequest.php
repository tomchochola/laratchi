<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Http\Requests\NonEmptySecureRequest;

class MeUpdateRequest extends NonEmptySecureRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response|bool
    {
        $this->retrieveUser();

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $authValidity = inject(AuthValidity::class);

        $guardName = $this->guardName();

        return [
            'guard' => $authValidity->guard()->nullable()->filled(),
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
     * Retrieve user.
     */
    public function retrieveUser(): AuthenticatableContract
    {
        return once(function (): AuthenticatableContract {
            return mustResolveUser([$this->guardName()]);
        });
    }
}
