<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tomchochola\Laratchi\Auth\Actions\CycleRememberTokenAction;
use Tomchochola\Laratchi\Auth\User;

/**
 * @mixin TestCase
 */
trait AuthControllersTestingHelpersTrait
{
    use WithFaker;

    /**
     * Create a new testing user.
     */
    protected function createUser(string $guardName): User
    {
        $user = $this->makeUser($guardName);

        static::assertTrue($user->save());

        return $user;
    }

    /**
     * Make a new testing user.
     */
    protected function makeUser(string $guardName): User
    {
        $user = new User();

        $user->forceFill([
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->email(),
            'email_verified_at' => Carbon::now(),
            'password' => resolveHashManager()->make($this->validPassword($guardName)),
        ]);

        inject(CycleRememberTokenAction::class)->handle($user);

        return $user;
    }

    /**
     * Valid password.
     */
    protected function validPassword(string $guardName): string
    {
        return 'password';
    }

    /**
     * Invalid password.
     */
    protected function invalidPassword(string $guardName): string
    {
        return $this->validPassword($guardName).'invalid';
    }

    /**
     * Get credentials for given user.
     *
     * @return array<string, mixed>
     */
    protected function credentials(string $guardName, User $user): array
    {
        return [
            'email' => $user->email,
        ];
    }
}
