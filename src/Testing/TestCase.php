<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Stringable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tomchochola\Laratchi\Auth\DatabaseTokenableInterface;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;
use Tomchochola\Laratchi\Exceptions\Handler;
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
        'Accept-Language' => 'en',
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
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
     * @inheritDoc
     *
     * @param array<mixed> $parameters
     * @param array<mixed> $cookies
     * @param array<mixed> $files
     * @param array<mixed> $server
     */
    public function call(mixed $method, mixed $uri, mixed $parameters = [], mixed $cookies = [], mixed $files = [], mixed $server = [], mixed $content = null): TestResponse
    {
        $params = $this->transformParameters($parameters);

        return parent::call($method, $uri, $params, $cookies, $files, $server, $content);
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
     * Set default auth guard.
     *
     * @return $this
     */
    protected function guard(string $guardName): static
    {
        resolveAuthManager()->shouldUse($guardName);

        return $this;
    }

    /**
     * Set default password broker.
     *
     * @return $this
     */
    protected function passwordBroker(string $passwordBrokerName): static
    {
        resolvePasswordBrokerManager()->setDefaultDriver($passwordBrokerName);

        return $this;
    }

    /**
     *  Login user using database token in header.
     *
     * @return $this
     */
    protected function beViaDatabaseToken(DatabaseTokenableInterface $user, string $guardName): static
    {
        $guard = resolveAuthManager()->guard($guardName);

        \assert($guard instanceof DatabaseTokenGuard);

        $databaseToken = $guard->createToken($user);

        return $this->withHeader('Authorization', "Bearer {$databaseToken->bearer}");
    }

    /**
     *  Login user with database token set.
     *
     * @return $this
     */
    protected function beWithDatabaseToken(DatabaseTokenableInterface $user, string $guardName): static
    {
        $guard = resolveAuthManager()->guard($guardName);

        \assert($guard instanceof DatabaseTokenGuard);

        $databaseToken = $guard->createToken($user);

        $user->setDatabaseToken($databaseToken);

        $guard->databaseToken = $databaseToken;

        return $this->be($user, $guardName);
    }

    /**
     * Assert json error.
     */
    protected function assertJsonError(TestResponse $response, int $status, int $code = 0, ?string $message = null, ?string $title = null): void
    {
        $response->assertStatus($status);

        $keys = [
            'status',
            'code',
        ];

        if (Handler::$genericErrors === false) {
            $keys[] = 'message';
            $keys[] = 'title';
        }

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

        if ($message !== null) {
            $data['message'] = $message;
        }

        if ($title !== null) {
            $data['title'] = $title;
        }

        $response->assertJson($data, true);

        $json = $response->json();

        \assert(\is_array($json));

        $this->validate(resolveValidatorFactory()->make($json, [
            'status' => Validity::make()->required()->unsigned(100, 599),
            'code' => Validity::make()->required()->unsigned(),
            'message' => Validity::make()->nullable()->filled()->raw()->requiredWith(['title']),
            'title' => Validity::make()->nullable()->filled()->raw()->requiredWith(['message']),
            'internal' => Validity::make()->nullable()->filled()->raw()->requiredIfRule($debug),
            'exception' => Validity::make()->nullable()->filled()->raw()->requiredIfRule($debug),
            'file' => Validity::make()->nullable()->filled()->raw()->requiredIfRule($debug),
            'line' => Validity::make()->nullable()->filled()->unsigned()->requiredIfRule($debug),
            'trace' => Validity::make()->collection(0, \PHP_INT_MAX)->requiredIfRule($debug),
            'trace.*' => Validity::make()->required()->object(),
            'trace.*.function' => Validity::make()->nullable()->raw(),
            'trace.*.line' => Validity::make()->nullable()->unsigned(),
            'trace.*.file' => Validity::make()->nullable()->raw(),
            'trace.*.class' => Validity::make()->nullable()->raw(),
            'trace.*.object' => Validity::make()->nullable()->object(),
            'trace.*.type' => Validity::make()->nullable()->raw(),
        ]));
    }

    /**
     * Assert json validation error.
     *
     * @param array<int, string>|array<string, array<int, string>> $errors
     */
    protected function assertJsonValidationError(TestResponse $response, array $errors, int $status = 422, int $code = 0, ?string $message = null, ?string $title = null): void
    {
        $this->assertJsonError($response, $status, $code, $message, $title);

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
            'errors.*' => Validity::make()->required()->collection(1, \PHP_INT_MAX),
            'errors.*.*' => Validity::make()->required()->raw(),
        ]));
    }

    /**
     * Resource json structure.
     *
     * @param array<mixed> $attributes
     * @param array<mixed> $meta
     *
     * @return array<mixed>
     */
    protected function jsonStructureResource(array $attributes = [], array $meta = []): array
    {
        $structure = [
            'id',
            'type',
            'slug',
        ];

        if (\count($attributes) > 0) {
            $structure['attributes'] = $attributes;
        }

        if (\count($meta) > 0) {
            $structure['meta'] = $meta;
        }

        return $structure;
    }

    /**
     * Relationship json structure.
     *
     * @param array<mixed> $meta
     *
     * @return array<mixed>
     */
    protected function jsonStructureRelationship(array $meta = []): array
    {
        $structure = [
            'data' => $this->jsonStructureResource(),
        ];

        if (\count($meta) > 0) {
            $structure['meta'] = $meta;
        }

        return $structure;
    }

    /**
     * Relationships json structure.
     *
     * @param array<mixed> $meta
     *
     * @return array<mixed>
     */
    protected function jsonStructureRelationships(array $meta = []): array
    {
        $structure = [
            'data' => [
                '*' => $this->jsonStructureResource(),
            ],
        ];

        if (\count($meta) > 0) {
            $structure['meta'] = $meta;
        }

        return $structure;
    }

    /**
     * Json api response assert.
     *
     * @param array<mixed> $dataStructure
     * @param array<mixed> $includedStructure
     */
    protected function assertJsonApiResponse(TestResponse $response, array $dataStructure = [], int $includedCount = 0, array $includedStructure = []): void
    {
        if (\count($dataStructure) === 0) {
            $dataStructure = $this->jsonStructureResource();
        }

        if (\count($includedStructure) === 0) {
            $includedStructure = $this->jsonStructureResource();
        }

        $response->assertJsonStructure([
            'data' => $dataStructure,
        ]);

        if ($includedCount === 0) {
            $response->assertJsonMissingPath('included');
        } elseif ($includedCount > 0) {
            $response->assertJsonStructure([
                'included' => [
                    '*' => $includedStructure,
                ],
            ]);

            $response->assertJsonCount($includedCount, 'included');
        }

        $json = $response->json();

        \assert(\is_array($json));

        $this->validate(resolveValidatorFactory()->make($json, [
            'data' => Validity::make()->nullable()->object()->requiredArrayKeys(['id', 'type', 'slug']),
            'data.id' => Validity::make()->nullable()->filled()->raw(),
            'data.type' => Validity::make()->nullable()->filled()->raw(),
            'data.slug' => Validity::make()->nullable()->filled()->raw(),
            'data.attributes' => Validity::make()->nullable()->filled()->object(),
            'data.meta' => Validity::make()->nullable()->filled()->object(),
            'data.relationships' => Validity::make()->nullable()->filled()->object(),
            'data.relationships.*' => Validity::make()->required()->object()->requiredArrayKeys(['data']),
            'data.relationships.*.data' => Validity::make()->nullable()->object(),
            'data.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'data.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
            'included' => Validity::make()->nullable()->filled()->collection(1, \PHP_INT_MAX),
            'included.*' => Validity::make()->required()->object()->requiredArrayKeys(['id', 'type', 'slug']),
            'included.*.id' => Validity::make()->required()->raw(),
            'included.*.type' => Validity::make()->required()->raw(),
            'included.*.slug' => Validity::make()->required()->raw(),
            'included.*.attributes' => Validity::make()->nullable()->filled()->object(),
            'included.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*' => Validity::make()->required()->object()->requiredArrayKeys(['data']),
            'included.*.relationships.*.data' => Validity::make()->nullable()->object(),
            'included.*.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
        ]));
    }

    /**
     * Json api collection response assert.
     *
     * @param array<mixed> $dataStructure
     * @param array<mixed> $includedStructure
     */
    protected function assertJsonApiCollectionResponse(TestResponse $response, array $dataStructure = [], int $dataCount = 1, int $includedCount = 0, array $includedStructure = []): void
    {
        if (\count($dataStructure) === 0) {
            $dataStructure = $this->jsonStructureResource();
        }

        if (\count($includedStructure) === 0) {
            $includedStructure = $this->jsonStructureResource();
        }

        $response->assertJsonStructure([
            'data' => [
                '*' => $dataStructure,
            ],
        ]);

        if ($dataCount >= 0) {
            $response->assertJsonCount($dataCount, 'data');
        }

        if ($includedCount === 0) {
            $response->assertJsonMissingPath('included');
        } elseif ($includedCount > 0) {
            $response->assertJsonStructure([
                'included' => [
                    '*' => $includedStructure,
                ],
            ]);

            $response->assertJsonCount($includedCount, 'included');
        }

        $json = $response->json();

        \assert(\is_array($json));

        $this->validate(resolveValidatorFactory()->make($json, [
            'data' => Validity::make()->collection(0, \PHP_INT_MAX),
            'data.*' => Validity::make()->required()->object()->requiredArrayKeys(['id', 'type', 'slug']),
            'data.*.id' => Validity::make()->required()->raw(),
            'data.*.type' => Validity::make()->required()->raw(),
            'data.*.slug' => Validity::make()->required()->raw(),
            'data.*.attributes' => Validity::make()->nullable()->filled()->object(),
            'data.*.meta' => Validity::make()->nullable()->filled()->object(),
            'data.*.relationships' => Validity::make()->nullable()->filled()->object(),
            'data.*.relationships.*' => Validity::make()->required()->object()->requiredArrayKeys(['data']),
            'data.*.relationships.*.data' => Validity::make()->nullable()->object(),
            'data.*.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'data.*.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
            'included' => Validity::make()->nullable()->filled()->collection(1, \PHP_INT_MAX),
            'included.*' => Validity::make()->required()->object()->requiredArrayKeys(['id', 'type', 'slug']),
            'included.*.id' => Validity::make()->required()->raw(),
            'included.*.type' => Validity::make()->required()->raw(),
            'included.*.slug' => Validity::make()->required()->raw(),
            'included.*.attributes' => Validity::make()->nullable()->filled()->object(),
            'included.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*' => Validity::make()->required()->object()->requiredArrayKeys(['data']),
            'included.*.relationships.*.data' => Validity::make()->nullable()->object(),
            'included.*.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
        ]));
    }

    /**
     * Make json api validator.
     *
     * @param ?array<string, mixed> $attributes
     * @param ?array<string, mixed> $meta
     */
    protected function jsonApiValidator(string $type, ?array $attributes = null, ?array $meta = null): JsonApiValidator
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
            'data' => Validity::make()->nullable()->object()->requiredArrayKeys(['id', 'type', 'slug']),
            'data.id' => Validity::make()->nullable()->filled()->raw(),
            'data.type' => Validity::make()->nullable()->filled()->raw(),
            'data.slug' => Validity::make()->nullable()->filled()->raw(),
            'data.attributes' => Validity::make()->nullable()->filled()->object(),
            'data.meta' => Validity::make()->nullable()->filled()->object(),
            'data.relationships' => Validity::make()->nullable()->filled()->object(),
            'data.relationships.*' => Validity::make()->required()->object()->requiredArrayKeys(['data']),
            'data.relationships.*.data' => Validity::make()->nullable()->object(),
            'data.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'data.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
            'included' => Validity::make()->nullable()->filled()->collection(1, \PHP_INT_MAX),
            'included.*' => Validity::make()->required()->object()->requiredArrayKeys(['id', 'type', 'slug']),
            'included.*.id' => Validity::make()->required()->raw(),
            'included.*.type' => Validity::make()->required()->raw(),
            'included.*.slug' => Validity::make()->required()->raw(),
            'included.*.attributes' => Validity::make()->nullable()->filled()->object(),
            'included.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*' => Validity::make()->required()->object()->requiredArrayKeys(['data']),
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
     * @param JsonApiValidator|array<int, JsonApiValidator> $validators
     * @param array<int, JsonApiValidator> $includedValidators
     */
    protected function validateJsonApiCollectionResponse(TestResponse $response, JsonApiValidator|array $validators, array $includedValidators): void
    {
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'type',
                    'slug',
                ],
            ],
        ]);

        if (\is_array($validators)) {
            $response->assertJsonCount(\count($validators), 'data');
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
            'data' => Validity::make()->collection(0, \PHP_INT_MAX),
            'data.*' => Validity::make()->required()->object()->requiredArrayKeys(['id', 'type', 'slug']),
            'data.*.id' => Validity::make()->required()->raw(),
            'data.*.type' => Validity::make()->required()->raw(),
            'data.*.slug' => Validity::make()->required()->raw(),
            'data.*.attributes' => Validity::make()->nullable()->filled()->object(),
            'data.*.meta' => Validity::make()->nullable()->filled()->object(),
            'data.*.relationships' => Validity::make()->nullable()->filled()->object(),
            'data.*.relationships.*' => Validity::make()->required()->object()->requiredArrayKeys(['data']),
            'data.*.relationships.*.data' => Validity::make()->nullable()->object(),
            'data.*.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'data.*.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
            'included' => Validity::make()->nullable()->filled()->collection(1, \PHP_INT_MAX),
            'included.*' => Validity::make()->required()->object()->requiredArrayKeys(['id', 'type', 'slug']),
            'included.*.id' => Validity::make()->required()->raw(),
            'included.*.type' => Validity::make()->required()->raw(),
            'included.*.slug' => Validity::make()->required()->raw(),
            'included.*.attributes' => Validity::make()->nullable()->filled()->object(),
            'included.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*' => Validity::make()->required()->object()->requiredArrayKeys(['data']),
            'included.*.relationships.*.data' => Validity::make()->nullable()->object(),
            'included.*.relationships.*.meta' => Validity::make()->nullable()->filled()->object(),
            'included.*.relationships.*.links' => Validity::make()->nullable()->filled()->object(),
        ]));

        $collection = $response->json('data');

        \assert(\is_array($collection));

        foreach ($collection as $index => $resource) {
            \assert(\is_array($resource));

            $this->validate(SecureValidator::clone(resolveValidatorFactory())->make($resource, \is_array($validators) ? $validators[$index]->rules() : $validators->rules()));
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
     * Validate validator.
     */
    protected function validate(Validator $validator): void
    {
        if ($validator->fails()) {
            static::assertEmpty($validator->failed(), 'Json api response validation failed: '.\json_encode($validator->failed()));
        }
    }
}
