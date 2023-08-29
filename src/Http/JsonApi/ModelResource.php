<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\JsonApi;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Tomchochola\Laratchi\Support\Typer;

/**
 * @template T of Model
 *
 * @extends ClosureResource<T>
 */
class ModelResource extends ClosureResource
{
    /**
     * Constructor.
     *
     * @param T $model
     * @param ?Closure(T): array<string, mixed> $closureAttributes
     * @param ?Closure(T): array<string, JsonApiRelationship> $closureRelationships
     * @param ?Closure(T): array<string, mixed> $closureMeta
     */
    public function __construct(Model $model, Closure|null $closureAttributes = null, Closure|null $closureRelationships = null, Closure|null $closureMeta = null)
    {
        parent::__construct(
            $model,
            static function (Model $model): string {
                return (string) Typer::assertScalar($model->getKey());
            },
            static function (Model $model): string {
                return (string) Typer::assertScalar($model->getRouteKey());
            },
            static function (Model $model): string {
                return $model->getTable();
            },
            $closureAttributes,
            $closureRelationships,
            $closureMeta,
        );
    }
}
