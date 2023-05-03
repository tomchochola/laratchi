<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\JsonApi;

use Closure;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class JsonApiResourceCollection
{
    /**
     * Constructor.
     *
     * @template T
     * @template B
     *
     * @param Collection<array-key, T>|CursorPaginator|Paginator $collection
     * @param Closure(B): JsonApiResource $closureMap
     */
    public function __construct(public Collection|CursorPaginator|Paginator $collection, public Closure $closureMap)
    {
    }

    /**
     * Get response.
     *
     * @param array<string, mixed> $meta
     * @param array<mixed> $headers
     */
    public function response(array $meta = [], int $status = 200, array $headers = []): JsonResponse
    {
        $included = collect();
        $collection = $this->collection instanceof Collection ? $this->collection : collect($this->collection->items());

        $data = [
            'data' => $collection->map(function (mixed $item) use ($included): array {
                return ($this->closureMap)($item)->data($included);
            })->values()->all(),
        ];

        if ($included->isNotEmpty()) {
            $data['included'] = $included->values()->all();
        }

        if ($this->collection instanceof CursorPaginator) {
            $meta = \array_replace($meta, [
                'next' => $this->collection->nextCursor()?->encode(),
                'prev' => $this->collection->previousCursor()?->encode(),
            ]);
        } elseif ($this->collection instanceof Paginator) {
            $meta = \array_replace($meta, [
                'next' => $this->collection->hasMorePages() ? $this->collection->currentPage() + 1 : null,
                'prev' => $this->collection->currentPage() !== 1 ? $this->collection->currentPage() - 1 : null,
            ]);
        }

        if ($this->collection instanceof LengthAwarePaginator) {
            $meta = \array_replace($meta, [
                'count' => $this->collection->total(),
                'prev' => $this->collection->currentPage() !== 1 ? $this->collection->currentPage() <= $this->collection->lastPage() ? $this->collection->currentPage() - 1 : $this->collection->lastPage() : null,
            ]);
        }

        if (\count($meta) > 0) {
            $data = \array_replace($data, ['meta' => $meta]);
        }

        return new JsonResponse($data, $status, $headers);
    }
}
