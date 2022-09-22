<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Stringable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tomchochola\Laratchi\Auth\DatabaseTokenableInterface;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;

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
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null): TestResponse
    {
        $this->transformParameters($parameters);

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    /**
     * Transform parameters.
     *
     * @param array<mixed> $parameters
     */
    protected function transformParameters(array &$parameters): void
    {
        foreach ($parameters as $key => $value) {
            if (\is_array($value)) {
                $this->transformParameters($value);
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
                \assert($value instanceof Stringable);

                $parameters[$key] = (string) $value;
            }
        }
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

        if ($message !== null) {
            $keys[] = 'message';
        }

        if ($title !== null) {
            $keys[] = 'title';
        }

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

        $json = $response->json() ?? [];

        \assert(\is_array($json));

        $jsonErrors = Arr::get($json, 'errors');

        foreach ($errors as $key => $value) {
            Arr::forget($jsonErrors, \is_int($key) ? $value : $key);
        }

        static::assertCount(0, $jsonErrors, 'Unexpected validation errors occured: '.\json_encode($jsonErrors).'.');
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
    }
}
