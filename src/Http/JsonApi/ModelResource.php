<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\JsonApi;

use Closure;
use Illuminate\Database\Eloquent\Model;

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
    public function __construct(Model $model, ?Closure $closureAttributes = null, ?Closure $closureRelationships = null, ?Closure $closureMeta = null)
    {
        parent::__construct($model, static function (Model $model): string {
            $id = $model->getKey();

            \assert(\is_scalar($id));

            return (string) $id;
        }, static function (Model $model): string {
            $id = $model->getRouteKey();

            \assert(\is_scalar($id));

            return (string) $id;
        }, static function (Model $model): string {
            return $model->getTable();
        }, $closureAttributes, $closureRelationships, $closureMeta);
    }
}
