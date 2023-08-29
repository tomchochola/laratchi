<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\JsonApi;

use Closure;

/**
 * @template T
 */
class ClosureResource extends JsonApiResource
{
    /**
     * Constructor.
     *
     * @param T $resource
     * @param Closure(T): string $closureId
     * @param Closure(T): string $closureSlug
     * @param Closure(T): string $closureType
     * @param ?Closure(T): array<string, mixed> $closureAttributes
     * @param ?Closure(T): array<string, JsonApiRelationship> $closureRelationships
     * @param ?Closure(T): array<string, mixed> $closureMeta
     */
    public function __construct(
        public mixed $resource,
        public Closure $closureId,
        public Closure $closureSlug,
        public Closure $closureType,
        public Closure|null $closureAttributes = null,
        public Closure|null $closureRelationships = null,
        public Closure|null $closureMeta = null,
    ) {}

    /**
     * @inheritDoc
     */
    public function attributes(): array|null
    {
        if ($this->closureAttributes === null) {
            return null;
        }

        return ($this->closureAttributes)($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function meta(): array|null
    {
        if ($this->closureMeta === null) {
            return null;
        }

        return ($this->closureMeta)($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function relationships(): array|null
    {
        if ($this->closureRelationships === null) {
            return null;
        }

        return ($this->closureRelationships)($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function id(): string
    {
        return ($this->closureId)($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function slug(): string
    {
        return ($this->closureSlug)($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return ($this->closureType)($this->resource);
    }
}
