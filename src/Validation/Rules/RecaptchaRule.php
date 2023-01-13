<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;

class RecaptchaRule implements RuleContract
{
    /**
     * Create a new rule instance.
     */
    public function __construct(protected string $secret, protected string $message = 'validation.invalid')
    {
    }

    /**
     * @inheritDoc
     */
    public function passes(mixed $attribute, mixed $value): bool
    {
        if (isEnv(['local', 'testing'])) {
            return true;
        }

        $response = resolveHttp()->acceptJson()->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $this->secret,
            'response' => $value,
        ]);

        if ($response->successful()) {
            return $response->json('success') === true;
        }

        return false;
    }

    /**
     * @inheritDoc
     *
     * @return string|array<int, string>
     */
    public function message(): string|array
    {
        return mustTransString($this->message);
    }
}
