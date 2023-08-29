<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RecaptchaRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(protected string $secret, protected string $message = 'validation.invalid') {}

    /**
     * @inheritDoc
     */
    public function validate(mixed $attribute, mixed $value, Closure $fail): void
    {
        if (\isEnv(['local', 'testing'])) {
            return;
        }

        $response = \resolveHttp()
            ->accept('application/json')
            ->asForm()
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $this->secret,
                'response' => $value,
            ]);

        if ($response->successful() && $response->json('success') === true) {
            return;
        }

        $fail(\mustTransString($this->message));
    }
}
