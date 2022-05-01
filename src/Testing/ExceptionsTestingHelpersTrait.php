<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Exceptions\Handler;

trait ExceptionsTestingHelpersTrait
{
    /**
     * Assert json error.
     */
    protected function assertJsonError(TestResponse $response, int $status, ?string $message = null, int $code = 0, ?string $title = null): void
    {
        $response->assertStatus($status);

        $keys = [
            'message',
            'title',
            'status',
            'code',
        ];

        if (resolveApp()->hasDebugModeEnabled()) {
            $keys = \array_merge($keys, [
                'exception',
                'file',
                'line',
                'trace',
                'internal',
            ]);
        }

        $response->assertJsonStructure($keys);

        $data = [
            'message' => $message ?? SymfonyResponse::$statusTexts[$status] ?? Handler::ERROR_MESSAGE_UNEXPECTED_ERROR,
            'status' => $status,
            'code' => $code,
        ];

        if ($title !== null) {
            $data['title'] = $title;
        }

        $response->assertJson($data, true);
    }

    /**
     * Assert json validation error.
     *
     * @param array<int, string>|array<string, array<int, string>> $errors
     */
    protected function assertJsonValidationError(TestResponse $response, array $errors, int $status = 422, string $message = 'The Given Data Was Invalid', int $code = 0, ?string $title = null): void
    {
        $this->assertJsonError($response, $status, $message, $code, $title);

        $response->assertJsonStructure(['errors']);

        $response->assertJsonValidationErrors($errors);

        $json = $response->json() ?? [];

        \assert(\is_array($json));

        $jsonErrors = Arr::get($json, 'errors');

        foreach ($errors as $key => $value) {
            Arr::forget($jsonErrors, \is_int($key) ? $value : $key);
        }

        static::assertCount(0, $jsonErrors, 'Unexpected validation errors occured: '.\json_encode($jsonErrors).'.');
    }
}
