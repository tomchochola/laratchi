<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Stringable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Validation\SecureValidator;
use Tomchochola\Laratchi\Validation\Validity;

abstract class TestCase extends BaseTestCase
{
    /**
     * @inheritDoc
     *
     * @var array<mixed>
     */
    protected $defaultHeaders = [
        'Accept' => 'application/json',
    ];

    /**
     * @inheritDoc
     */
    protected $encryptCookies = false;

    /**
     * @inheritDoc
     */
    protected $withCredentials = true;

    /**
     * Locale method called.
     */
    private bool $localeCalled = false;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        \assert($this->localeCalled, '$this->locale($locale) must be called in every test!');

        $this->localeCalled = false;
    }

    /**
     * Locale data provider.
     *
     * @return array<string, array{string}>
     */
    public function localeDataProvider(): array
    {
        return [
            'en' => [
                'en',
            ],
        ];
    }

    /**
     * Locale bool data provider.
     *
     * @return array<string, array{string,bool}>
     */
    public function localeBoolDataProvider(): array
    {
        return [
            'en|false' => [
                'en',
                false,
            ],
            'en|true' => [
                'en',
                true,
            ],
        ];
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $parameters
     * @param array<mixed> $cookies
     * @param array<mixed> $files
     * @param array<mixed> $server
     */
    public function call(mixed $method, mixed $uri, mixed $parameters = [], mixed $cookies = [], mixed $files = [], mixed $server = [], mixed $content = null): TestResponse
    {
        \assert(\in_array($method, ['GET', 'POST'], true), 'Only GET and POST method is allowed');

        $params = $this->transformParameters($parameters);

        return parent::call($method, $uri, $params, $cookies, $files, $server, $content);
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function json(mixed $method, mixed $uri, array $data = [], array $headers = []): never
    {
        assertNever();
    }

    /**
     * @inheritDoc
     *
     * @return $this
     */
    public function be(Authenticatable $user, mixed $guard = null): static
    {
        \assert($user instanceof User);
        \assert($guard === null || $guard === $user->getUserProviderName());

        $user->wasRecentlyCreated = false;

        resolveAuthManager()->guard($user->getUserProviderName())->setUser($user);

        return $this;
    }

    /**
     * Transform parameters.
     *
     * @param array<mixed> $parameters
     *
     * @return array<mixed>
     */
    protected function transformParameters(array $parameters): array
    {
        foreach ($parameters as $key => $value) {
            if (\is_array($value)) {
                $parameters[$key] = $this->transformParameters($value);
            } elseif ($value instanceof UploadedFile) {
                continue;
            } elseif (\is_bool($value)) {
                $parameters[$key] = $value === true ? '1' : '0';
            } elseif (\is_string($value)) {
                continue;
            } elseif ($value === null) {
                $parameters[$key] = '';
            } elseif (\is_scalar($value)) {
                $parameters[$key] = (string) $value;
            } else {
                \assert($value instanceof Stringable, "Can not convert [{$key}] to multipart/form-data.");

                $parameters[$key] = (string) $value;
            }
        }

        return $parameters;
    }

    /**
     * Validate json error.
     */
    protected function validateJsonApiError(TestResponse $response, int $status, int $code = 0): void
    {
        \assert($status >= 400 && $status <= 599);
        \assert($code >= 0);

        $response->assertStatus($status);

        $keys = [
            'status',
            'code',
        ];

        $debug = resolveApp()->hasDebugModeEnabled();

        if ($debug) {
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
            'status' => $status,
            'code' => $code,
        ];

        $response->assertJson($data, true);

        $json = $response->json();

        \assert(\is_array($json));

        $this->validate(resolveValidatorFactory()->make($json, [
            'status' => Validity::make()->required()->unsigned(100, 599),
            'code' => Validity::make()->required()->unsigned(),
            'internal' => Validity::make()->nullable()->filled()->string()->requiredIfRule($debug),
            'exception' => Validity::make()->nullable()->filled()->string()->requiredIfRule($debug),
            'file' => Validity::make()->nullable()->filled()->string()->requiredIfRule($debug),
            'line' => Validity::make()->nullable()->filled()->unsigned()->requiredIfRule($debug),
            'trace' => Validity::make()->array()->requiredIfRule($debug),
            'trace.*' => Validity::make()->required()->object(),
            'trace.*.function' => Validity::make()->nullable()->string(),
            'trace.*.line' => Validity::make()->nullable()->unsigned(),
            'trace.*.file' => Validity::make()->nullable()->string(),
            'trace.*.class' => Validity::make()->nullable()->string(),
            'trace.*.object' => Validity::make()->nullable()->object(),
            'trace.*.type' => Validity::make()->nullable()->string(),
        ]));
    }

    /**
     * Validate json validation error.
     *
     * @param array<int, string>|array<string, array<int, string>> $errors
     */
    protected function validateJsonApiValidationError(TestResponse $response, array $errors, int $status = 422, int $code = 0): void
    {
        $this->validateJsonApiError($response, $status, $code);

        $response->assertJsonStructure(['errors']);

        $response->assertJsonValidationErrors($errors);

        $json = $response->json();

        \assert(\is_array($json));

        $jsonErrors = Arr::get($json, 'errors');

        foreach ($errors as $key => $value) {
            Arr::forget($jsonErrors, \is_int($key) ? $value : $key);
        }

        static::assertCount(0, $jsonErrors, 'Unexpected validation errors occured: '.\json_encode($jsonErrors).'.');

        $this->validate(resolveValidatorFactory()->make($json, [
            'errors' => Validity::make()->required()->object(),
            'errors.*' => Validity::make()->required()->array(),
            'errors.*.*' => Validity::make()->required()->string(),
        ]));
    }

    /**
     * Make json api validator.
     *
     * @param ?array<string, mixed> $attributes
     * @param ?array<string, mixed> $meta
     */
    protected function jsonApiValidator(?string $type, ?array $attributes = null, ?array $meta = null): JsonApiValidator
    {
        return $this->structure($type, $attributes, $meta);
    }

    /**
     * Make json api validator.
     *
     * @param ?array<string, mixed> $attributes
     * @param ?array<string, mixed> $meta
     */
    protected function structure(?string $type, ?array $attributes = null, ?array $meta = null): JsonApiValidator
    {
        return new JsonApiValidator($type, $attributes, $meta);
    }

    /**
     * Json api response validation.
     *
     * @param array<int, JsonApiValidator> $includedValidators
     */
    protected function validateJsonApiResponse(TestResponse $response, ?JsonApiValidator $validator, array $includedValidators): void
    {
        $response->assertSuccessful();

        $response->assertJsonStructure(['data']);

        if ($validator !== null) {
            $response->assertJsonStructure([
                'data' => [
                    'id',
                    'type',
                    'slug',
                ],
            ]);
        } else {
            $response->assertJsonPath('data', null);
        }

        $includedCount = \count($includedValidators);

        if ($includedCount === 0) {
            $response->assertJsonMissingPath('included');
        } else {
            $response->assertJsonStructure([
                'included' => [
                    '*' => [
                        'id',
                        'type',
                        'slug',
                    ],
                ],
            ]);

            $response->assertJsonCount($includedCount, 'included');
        }

        $json = $response->json();

        \assert(\is_array($json));

        $this->validate(resolveValidatorFactory()->make($json, [
            'data' => Validity::make()->nullable()->object(['id', 'type', 'slug']),
            'data.id' => Validity::make()->nullable()->filled()->string(),
            'data.type' => Validity::make()->nullable()->filled()->string(),
            'data.slug' => Validity::make()->nullable()->filled()->string(),
            'data.attributes' => Validity::make()->nullable()->filled()->object(),
            'data.meta' => Validity::make()->nullable()->filled()->object(),
            'data.relationships' => Validity::make()->nullable()->filled()->object(),
            'data.relationships.*' => Validity::make()->required()->object(['data']),
            'data.relationships.*.data' => Validity::make()->nullable()->object(),
            'data.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'data.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
            'included' => Validity::make()->nullable()->filled()->array(),
            'included.*' => Validity::make()->required()->object(['id', 'type', 'slug']),
            'included.*.id' => Validity::make()->required()->string(),
            'included.*.type' => Validity::make()->required()->string(),
            'included.*.slug' => Validity::make()->required()->string(),
            'included.*.attributes' => Validity::make()->nullable()->filled()->object(),
            'included.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*' => Validity::make()->required()->object(['data']),
            'included.*.relationships.*.data' => Validity::make()->nullable()->object(),
            'included.*.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
        ]));

        if ($validator !== null) {
            $resource = $response->json('data');

            \assert(\is_array($resource));

            $this->validate(SecureValidator::clone(resolveValidatorFactory())->make($resource, $validator->rules()));
        }

        $included = $response->json('included') ?? [];

        \assert(\is_array($included));

        foreach ($includedValidators as $index => $includedValidator) {
            $resource = $included[$index];

            \assert(\is_array($resource));

            $this->validate(SecureValidator::clone(resolveValidatorFactory())->make($resource, $includedValidator->rules()));
        }
    }

    /**
     * Json api collection response validation.
     *
     * @param array<int, JsonApiValidator> $validators
     * @param array<int, JsonApiValidator> $includedValidators
     */
    protected function validateJsonApiCollectionResponse(TestResponse $response, array $validators, array $includedValidators): void
    {
        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'type',
                    'slug',
                ],
            ],
        ]);

        $response->assertJsonCount(\count($validators), 'data');

        $includedCount = \count($includedValidators);

        if ($includedCount === 0) {
            $response->assertJsonMissingPath('included');
        } else {
            $response->assertJsonStructure([
                'included' => [
                    '*' => [
                        'id',
                        'type',
                        'slug',
                    ],
                ],
            ]);

            $response->assertJsonCount($includedCount, 'included');
        }

        $json = $response->json();

        \assert(\is_array($json));

        $this->validate(resolveValidatorFactory()->make($json, [
            'data' => Validity::make()->array(),
            'data.*' => Validity::make()->required()->object(['id', 'type', 'slug']),
            'data.*.id' => Validity::make()->required()->string(),
            'data.*.type' => Validity::make()->required()->string(),
            'data.*.slug' => Validity::make()->required()->string(),
            'data.*.attributes' => Validity::make()->nullable()->filled()->object(),
            'data.*.meta' => Validity::make()->nullable()->filled()->object(),
            'data.*.relationships' => Validity::make()->nullable()->filled()->object(),
            'data.*.relationships.*' => Validity::make()->required()->object(['data']),
            'data.*.relationships.*.data' => Validity::make()->nullable()->object(),
            'data.*.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'data.*.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
            'included' => Validity::make()->nullable()->filled()->array(),
            'included.*' => Validity::make()->required()->object(['id', 'type', 'slug']),
            'included.*.id' => Validity::make()->required()->string(),
            'included.*.type' => Validity::make()->required()->string(),
            'included.*.slug' => Validity::make()->required()->string(),
            'included.*.attributes' => Validity::make()->nullable()->filled()->object(),
            'included.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*' => Validity::make()->required()->object(['data']),
            'included.*.relationships.*.data' => Validity::make()->nullable()->object(),
            'included.*.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
        ]));

        $collection = $response->json('data');

        \assert(\is_array($collection));

        foreach ($collection as $index => $resource) {
            \assert(\is_array($resource));

            $this->validate(SecureValidator::clone(resolveValidatorFactory())->make($resource, $validators[$index]->rules()));
        }

        $included = $response->json('included') ?? [];

        \assert(\is_array($included));

        foreach ($includedValidators as $index => $includedValidator) {
            $resource = $included[$index];

            \assert(\is_array($resource));

            $this->validate(SecureValidator::clone(resolveValidatorFactory())->make($resource, $includedValidator->rules()));
        }
    }

    /**
     * Set locale.
     */
    protected function locale(string $locale): void
    {
        \assert(\in_array($locale, mustConfigArray('app.locales'), true));

        $app = resolveApp();

        $app->setLocale($locale);
        $app->setFallbackLocale($locale);

        $this->defaultHeaders['Accept-Language'] = $locale;

        $this->localeCalled = true;
    }

    /**
     * Validate validator.
     */
    private function validate(ValidatorContract $validator): void
    {
        if ($validator->fails()) {
            static::assertEmpty($validator->failed(), 'Json api response validation failed: '.\json_encode($validator->failed()));
        }
    }
}
