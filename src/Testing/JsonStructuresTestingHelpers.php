<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;

trait JsonStructuresTestingHelpers
{
    /**
     * Resource json structure.
     *
     * @param array<mixed> $attributes
     *
     * @return array<mixed>
     */
    public function jsonStructureResource(array $attributes = []): array
    {
        $structure = [
            'id',
            'type',
            'slug',
        ];

        if (\count($attributes) > 0) {
            $structure['attributes'] = $attributes;
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
    public function jsonStructureRelationship(array $meta = []): array
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
    public function jsonStructureRelationships(array $meta = []): array
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
     * Me json structure.
     *
     * @return array<mixed>
     */
    public function jsonStructureMe(bool $withDatabaseToken = false): array
    {
        $structure = $this->jsonStructureResource([
            'name',
            'email',
            'email_verified_at',
            'locale',
            'created_at',
            'updated_at',
        ]);

        if ($withDatabaseToken) {
            Arr::set($structure, 'relationships.database_token', $this->jsonStructureRelationship());
        }

        return $structure;
    }

    /**
     * Database token json structure.
     *
     * @return array<mixed>
     */
    public function jsonStructureDatabaseToken(bool $withBearer = false): array
    {
        $structure = $this->jsonStructureResource([
            'provider',
            'auth_id',
            'created_at',
            'updated_at',
        ]);

        \assert(\is_array($structure['attributes']));

        if ($withBearer) {
            $structure['attributes'][] = 'bearer';
        }

        return $structure;
    }

    /**
     * Json api response assert.
     *
     * @param array<mixed> $dataStructure
     * @param array<mixed> $includedStructure
     */
    public function assertJsonApiResponse(TestResponse $response, array $dataStructure = [], int $includedCount = 0, array $includedStructure = []): void
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

        if ($includedCount <= 0) {
            $response->assertJsonMissingPath('included');
        } else {
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
    public function assertJsonApiCollectionResponse(TestResponse $response, int $dataCount, array $dataStructure = [], int $includedCount = 0, array $includedStructure = []): void
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

        $response->assertJsonCount($dataCount, 'data');

        if ($includedCount <= 0) {
            $response->assertJsonMissingPath('included');
        } else {
            $response->assertJsonStructure([
                'included' => [
                    '*' => $includedStructure,
                ],
            ]);

            $response->assertJsonCount($includedCount, 'included');
        }
    }
}
