<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Resources;

use Closure;
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
     * @param Collection<array-key, T>|AbstractPaginator|AbstractCursorPaginator $resource
     * @param (Closure(T): (class-string<JsonApiResource>|JsonApiResource))|class-string<JsonApiResource> $collectsClosure
     */
    public function __construct(Collection|AbstractPaginator|AbstractCursorPaginator $resource, protected Closure|string $collectsClosure)
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
     * @return Collection<array-key, JsonApiResource>|AbstractPaginator|AbstractCursorPaginator
     */
    protected function collectResource(mixed $resource): Collection|AbstractPaginator|AbstractCursorPaginator
    {
        \assert($resource instanceof Collection || $resource instanceof AbstractCursorPaginator || $resource instanceof AbstractPaginator);

        $this->collection = $resource->map(function (mixed $object): object {
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
