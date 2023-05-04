<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @template T of User
 *
 * @extends Factory<T>
 */
class UserFactory extends Factory
{
    public const PASSWORD = 'password';

    /**
     * @inheritDoc
     */
    protected $model = User::class;

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => resolveDate()->now(),
            'password' => Str::random(),
            'remember_token' => Str::random(),
            'locale' => resolveApp()->getLocale(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->set('email_verified_at', null);
    }

    /**
     * Create model with password filled.
     */
    public function password(?string $password = null): static
    {
        return $this->set('password', resolveHasher()->make($password ?? static::PASSWORD));
    }

    /**
     * Create model with given locale.
     */
    public function locale(string $locale): static
    {
        return $this->set('locale', $locale);
    }

    /**
     * Create model with random locale.
     */
    public function randomLocale(): static
    {
        return $this->set('locale', fake()->randomElement(mustConfigArray('app.locales')));
    }

    /**
     * Create model with blank password.
     */
    public function blankPassword(): static
    {
        return $this->set('password', null);
    }

    /**
     * Create model with blank remember token.
     */
    public function blankRememberToken(): static
    {
        return $this->set('remember_token', null);
    }
}
