<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\JsonApi;

use Closure;
use Illuminate\Support\Collection;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Database\Model;
use Tomchochola\Laratchi\Database\Pivot;

class ModelRelationship extends JsonApiRelationship
{
    /**
     * Constructor.
     *
     * @template T of Model|User|Pivot
     *
     * @param T|Collection<array-key, T>|null $resource
     * @param ?Closure(): array<string, mixed> $closureMeta
     */
    public function __construct(mixed $resource, ?Closure $closureMeta = null)
    {
        parent::__construct($resource, static fn (Model|User|Pivot $model): JsonApiResource => $model->embedResource(), $closureMeta);
    }
}
