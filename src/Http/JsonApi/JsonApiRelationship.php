<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\JsonApi;

use Closure;
use Illuminate\Support\Collection;

class JsonApiRelationship
{
    /**
     * Constructor.
     *
     * @template T
     * @template B
     *
     * @param Collection<array-key, T>|T|null $resource
     * @param Closure(B): JsonApiResource $closureMap
     * @param ?Closure(): array<string, mixed> $closureMeta
     */
    public function __construct(public mixed $resource, public Closure $closureMap, public Closure|null $closureMeta = null) {}

    /**
     * Get meta.
     *
     * @return ?array<string, mixed>
     */
    public function meta(): array|null
    {
        if ($this->closureMeta === null) {
            return null;
        }

        return ($this->closureMeta)();
    }
}
