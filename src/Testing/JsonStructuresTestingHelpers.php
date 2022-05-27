<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Support\Arr;

trait JsonStructuresTestingHelpers
{
    /**
     * Resource json structure.
     *
     * @return array<mixed>
     */
    public function jsonStructureResource(): array
    {
        return [
            'id',
            'type',
            'slug',
        ];
    }

    /**
     * Relationship json structure.
     *
     * @return array<mixed>
     */
    public function jsonStructureRelationship(): array
    {
        return [
            'data' => $this->jsonStructureResource(),
        ];
    }

    /**
     * Relationships json structure.
     *
     * @return array<mixed>
     */
    public function jsonStructureRelationships(): array
    {
        return [
            'data' => [
                '*' => $this->jsonStructureResource(),
            ],
        ];
    }

    /**
     * Me json structure.
     *
     * @return array<mixed>
     */
    public function jsonStructureMe(bool $withDatabaseToken = false): array
    {
        $structure = \array_merge($this->jsonStructureResource(), [
            'attributes' => [
                'name',
                'email',
                'email_verified_at',
                'locale',
                'created_at',
                'updated_at',
            ],
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
        $structure = \array_merge($this->jsonStructureResource(), [
            'attributes' => [
                'provider',
                'auth_id',
                'created_at',
                'updated_at',
            ],
        ]);

        \assert(\is_array($structure['attributes']));

        if ($withBearer) {
            $structure['attributes'][] = 'bearer';
        }

        return $structure;
    }
}
