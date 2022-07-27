<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Resources;

use Closure;
use Illuminate\Contracts\Pagination\CursorPaginator as CursorPaginatorContract;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

/**
 * @template T
 *
 * @property Collection<array-key, JsonApiResource> $collection
 */
class JsonApiCollectionResponse extends AnonymousResourceCollection
{
    /**
     * @inheritDoc
     *
     * @param Collection<array-key, T>|PaginatorContract|CursorPaginatorContract $resource
     * @param (Closure(T): (class-string<JsonApiResource>|JsonApiResource))|class-string<JsonApiResource> $collectsClosure
     */
    public function __construct(Collection|PaginatorContract|CursorPaginatorContract $resource, protected Closure|string $collectsClosure)
    {
        parent::__construct($resource, '');
    }

    /**
     * @inheritDoc
     *
     * @return array<string, mixed>
     */
    public function with(mixed $request): array
    {
        $included = [];

        foreach ($this->collection as $resource) {
            JsonApiResource::withIncluded($resource->getIncluded(), $included, $request);
        }

        if (\count($included) > 0) {
            $this->with['included'] = \array_values($included);
        }

        return parent::with($request);
    }

    /**
     * @inheritDoc
     *
     * @return Collection<array-key, JsonApiResource>|PaginatorContract|CursorPaginatorContract
     */
    protected function collectResource(mixed $resource): Collection|PaginatorContract|CursorPaginatorContract
    {
        \assert($resource instanceof Collection || $resource instanceof PaginatorContract || $resource instanceof CursorPaginatorContract);

        $items = $resource instanceof Collection ? $resource : collect($resource->items());

        $this->collection = $items->map(function (mixed $object): object {
            if (\is_string($this->collectsClosure)) {
                $class = $this->collectsClosure;

                if ($object instanceof $class) {
                    return $object;
                }

                return new $class($object);
            }

            $class = ($this->collectsClosure)($object);

            if (\is_string($class)) {
                if ($object instanceof $class) {
                    return $object;
                }

                return new $class($object);
            }

            return $class;
        });

        return ($resource instanceof AbstractPaginator || $resource instanceof AbstractCursorPaginator)
            ? $resource->setCollection($this->collection)
            : $this->collection;
    }
}
