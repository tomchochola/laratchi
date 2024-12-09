<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use BackedEnum;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use JsonSerializable;
use Stringable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Exceptions\Panicker;
use Tomchochola\Laratchi\Support\Typer;
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
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
     * @inheritDoc
     *
     * @return $this
     */
    public function be(Authenticatable $user, mixed $guard = null): static
    {
        $auth = Typer::assertInstance($user, User::class);

        $auth->wasRecentlyCreated = false;

        \resolveAuthManager()
            ->guard($guard ?? $auth->getTable())
            ->setUser($auth);

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
                $parameters[$key] = \count($value) === 0 ? '' : $this->transformParameters($value);
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
            } elseif ($value instanceof BackedEnum) {
                $parameters[$key] = $value->value;
            } elseif ($value instanceof Carbon) {
                $parameters[$key] = $value->toJSON();
            } elseif ($value instanceof DateTimeInterface) {
                $parameters[$key] = $value->format('Y-m-d\\TH:i:s.up');
            } elseif ($value instanceof JsonSerializable) {
                $parameters[$key] = $value->jsonSerialize();
            } elseif ($value instanceof Stringable) {
                $parameters[$key] = $value->__toString();
            } elseif (\is_object($value)) {
                $parameters[$key] = \count(\get_object_vars($value)) === 0 ? '' : $this->transformParameters(\get_object_vars($value));
            } else {
                Panicker::panic(__METHOD__, 'not multipart/form-data encodable', \compact('key', 'value'));
            }
        }

        return $parameters;
    }

    /**
     * Validate json error.
     */
    protected function validateJsonApiError(TestResponse $response, int $status, int $code = 0): void
    {
        $response->assertStatus($status);

        $keys = ['code'];

        $response->assertJsonStructure($keys);

        $data = [
            'code' => $code,
        ];

        $response->assertJson($data, true);

        $json = Typer::assertArray($response->json());

        $json = Arr::except($json, ['exception', 'file', 'line', 'trace', 'internal']);

        $this->validate(
            \resolveValidatorFactory()->make($json, [
                'code' => Validity::make()
                    ->required()
                    ->inInteger([$code]),
            ]),
        );
    }

    /**
     * Validate json validation error.
     *
     * @param array<int, string>|array<string, array<int, string>> $errors
     */
    protected function validateJsonApiValidationError(TestResponse $response, array $errors, int $code = 0): void
    {
        $response->assertUnprocessable();

        $this->validateJsonApiError($response, 422, $code);

        $response->assertJsonStructure(['errors']);

        $response->assertJsonValidationErrors($errors);

        $json = Typer::assertArray($response->json());

        $json = Arr::except($json, ['exception', 'file', 'line', 'trace', 'internal']);

        $jsonErrors = Typer::assertArray(Arr::get($json, 'errors'));

        foreach ($errors as $key => $value) {
            Arr::forget($jsonErrors, \is_int($key) ? $value : $key);
        }

        static::assertCount(0, $jsonErrors, 'Unexpected validation errors occurred: ' . \json_encode($jsonErrors) . '.');

        $this->validate(
            \resolveValidatorFactory()->make($json, [
                'errors' => Validity::make()->required()->array(null),
                'errors.*' => Validity::make()->required()->collection(null),
                'errors.*.*' => Validity::make()->required()->string(null),
            ]),
        );
    }

    /**
     * Make json api validator.
     *
     * @param ?array<string, mixed> $attributes
     * @param ?array<string, mixed> $meta
     */
    protected function jsonApiValidator(string|null $type, array|null $attributes = null, array|null $meta = null): JsonApiValidator
    {
        return $this->structure($type, $attributes, $meta);
    }

    /**
     * Make json api validator.
     *
     * @param ?array<string, mixed> $attributes
     * @param ?array<string, mixed> $meta
     */
    protected function structure(string|null $type, array|null $attributes = null, array|null $meta = null): JsonApiValidator
    {
        return new JsonApiValidator($type, $attributes, $meta);
    }

    /**
     * Json api response validation.
     *
     * @param array<int, JsonApiValidator> $includedValidators
     */
    protected function validateJsonApiResponse(TestResponse $response, JsonApiValidator|null $validator, array $includedValidators): void
    {
        $response->assertSuccessful();

        $response->assertJsonStructure(['data']);

        if ($validator !== null) {
            $response->assertJsonStructure([
                'data' => ['id', 'type', 'slug'],
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
                    '*' => ['id', 'type', 'slug'],
                ],
            ]);

            $response->assertJsonCount($includedCount, 'included');
        }

        $json = Typer::assertArray($response->json());

        $this->validate(
            \resolveValidatorFactory()->make($json, [
                'data' => Validity::make()
                    ->nullable()
                    ->object(['id', 'type', 'slug']),
                'data.id' => Validity::make()->nullable()->filled()->string(null),
                'data.type' => Validity::make()->nullable()->filled()->string(null),
                'data.slug' => Validity::make()->nullable()->filled()->string(null),
                'data.attributes' => Validity::make()->nullable()->filled()->array(null),
                'data.meta' => Validity::make()->nullable()->filled()->array(null),
                'data.relationships' => Validity::make()->nullable()->filled()->array(null),
                'data.relationships.*' => Validity::make()
                    ->required()
                    ->object(['data']),
                'data.relationships.*.data' => Validity::make()->nullable()->array(null),
                'data.relationships.*.meta' => Validity::make()->nullable()->filled()->array(null),
                'data.relationships.*.links' => Validity::make()->nullable()->filled()->array(null),
                'included' => Validity::make()->nullable()->filled()->collection(null),
                'included.*' => Validity::make()
                    ->required()
                    ->object(['id', 'type', 'slug']),
                'included.*.id' => Validity::make()->required()->string(null),
                'included.*.type' => Validity::make()->required()->string(null),
                'included.*.slug' => Validity::make()->required()->string(null),
                'included.*.attributes' => Validity::make()->nullable()->filled()->array(null),
                'included.*.meta' => Validity::make()->nullable()->filled()->array(null),
                'included.*.relationships' => Validity::make()->nullable()->filled()->array(null),
                'included.*.relationships.*' => Validity::make()
                    ->required()
                    ->object(['data']),
                'included.*.relationships.*.data' => Validity::make()->nullable()->array(null),
                'included.*.relationships.*.meta' => Validity::make()->nullable()->filled()->array(null),
                'included.*.relationships.*.links' => Validity::make()->nullable()->filled()->array(null),
            ]),
        );

        if ($validator !== null) {
            $resource = Typer::assertArray($response->json('data'));

            $this->validate(SecureValidator::clone(\resolveValidatorFactory())->make($resource, $validator->rules()));
        }

        $included = Typer::assertNullableArray($response->json('included')) ?? [];

        foreach ($includedValidators as $index => $includedValidator) {
            $resource = Typer::assertArray($included[$index]);

            $this->validate(SecureValidator::clone(\resolveValidatorFactory())->make($resource, $includedValidator->rules()));
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
                '*' => ['id', 'type', 'slug'],
            ],
        ]);

        $response->assertJsonCount(\count($validators), 'data');

        $includedCount = \count($includedValidators);

        if ($includedCount === 0) {
            $response->assertJsonMissingPath('included');
        } else {
            $response->assertJsonStructure([
                'included' => [
                    '*' => ['id', 'type', 'slug'],
                ],
            ]);

            $response->assertJsonCount($includedCount, 'included');
        }

        $json = Typer::assertArray($response->json());

        $this->validate(
            \resolveValidatorFactory()->make($json, [
                'data' => Validity::make()->nullable()->collection(null),
                'data.*' => Validity::make()
                    ->required()
                    ->object(['id', 'type', 'slug']),
                'data.*.id' => Validity::make()->required()->string(null),
                'data.*.type' => Validity::make()->required()->string(null),
                'data.*.slug' => Validity::make()->required()->string(null),
                'data.*.attributes' => Validity::make()->nullable()->filled()->array(null),
                'data.*.meta' => Validity::make()->nullable()->filled()->array(null),
                'data.*.relationships' => Validity::make()->nullable()->filled()->array(null),
                'data.*.relationships.*' => Validity::make()
                    ->required()
                    ->object(['data']),
                'data.*.relationships.*.data' => Validity::make()->nullable()->array(null),
                'data.*.relationships.*.meta' => Validity::make()->nullable()->filled()->array(null),
                'data.*.relationships.*.links' => Validity::make()->nullable()->filled()->array(null),
                'included' => Validity::make()->nullable()->filled()->collection(null),
                'included.*' => Validity::make()
                    ->required()
                    ->object(['id', 'type', 'slug']),
                'included.*.id' => Validity::make()->required()->string(null),
                'included.*.type' => Validity::make()->required()->string(null),
                'included.*.slug' => Validity::make()->required()->string(null),
                'included.*.attributes' => Validity::make()->nullable()->filled()->array(null),
                'included.*.meta' => Validity::make()->nullable()->filled()->array(null),
                'included.*.relationships' => Validity::make()->nullable()->filled()->array(null),
                'included.*.relationships.*' => Validity::make()
                    ->required()
                    ->object(['data']),
                'included.*.relationships.*.data' => Validity::make()->nullable()->array(null),
                'included.*.relationships.*.meta' => Validity::make()->nullable()->filled()->array(null),
                'included.*.relationships.*.links' => Validity::make()->nullable()->filled()->array(null),
            ]),
        );

        $collection = Typer::assertArray($response->json('data'));

        foreach ($collection as $index => $resource) {
            $this->validate(SecureValidator::clone(\resolveValidatorFactory())->make(Typer::assertArray($resource), $validators[$index]->rules()));
        }

        $included = Typer::assertNullableArray($response->json('included')) ?? [];

        foreach ($includedValidators as $index => $includedValidator) {
            $resource = Typer::assertArray($included[$index]);

            $this->validate(SecureValidator::clone(\resolveValidatorFactory())->make($resource, $includedValidator->rules()));
        }
    }

    /**
     * Set locale.
     */
    protected function locale(string $locale): void
    {
        $config = Config::inject();

        $config->setAppLocale($locale);
        $config->setAppFallbackLocale($locale);

        $this->defaultHeaders['Accept-Language'] = $locale;
    }

    /**
     * Validate validator.
     */
    private function validate(ValidatorContract $validator): void
    {
        if ($validator->fails()) {
            static::assertEmpty($validator->failed(), 'Json api response validation failed: ' . \json_encode($validator->failed()));
        }
    }
}
