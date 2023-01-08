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
    public const VALID_PASSWORD = 'password';

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
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => resolveDate()->now(),
            'password' => Str::random(10),
            'remember_token' => Str::random(10),
            'locale' => $this->faker->randomElement(mustConfigArray('app.locales')),
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
     * Create model with valid password filled.
     */
    public function withValidPassword(): static
    {
        return $this->set('password', resolveHasher()->make(static::VALID_PASSWORD));
    }
}
