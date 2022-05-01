<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property Model $resource
 */
class ModelJsonApiResource extends JsonApiResource
{
    /**
     * @inheritDoc
     */
    public function __construct(Model $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        $id = $this->resource->getKey();

        \assert(\is_scalar($id));

        return (string) $id;
    }

    /**
     * @inheritDoc
     */
    public function getSlug(): string
    {
        $slug = $this->resource->getRouteKey();

        \assert(\is_scalar($slug));

        return (string) $slug;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return Str::snake($this->resource->getTable());
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->resource->attributesToArray();
    }

    /**
     * @inheritDoc
     */
    public function getRelationships(): array
    {
        $relations = $this->resource->getRelations();

        if (\count($this->resource->getVisible()) > 0) {
            $relations = \array_intersect_key($relations, \array_flip($this->resource->getVisible()));
        }

        if (\count($this->resource->getHidden()) > 0) {
            $relations = \array_diff_key($relations, \array_flip($this->resource->getHidden()));
        }

        return \array_map(static fn (Model|Collection|null $resource): null|array|self => $resource === null ? null : ($resource instanceof Collection ? $resource->mapInto(self::class)->all() : new self($resource)), $relations);
    }
}
