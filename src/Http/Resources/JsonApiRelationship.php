<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Resources;

use Closure;
use Illuminate\Contracts\Support\Arrayable as ArrayableContract;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * @property JsonApiResource|JsonApiCollectionResponse|null $resource
 */
class JsonApiRelationship extends JsonResource
{
    /**
     * @inheritDoc
     *
     * @template T
     *
     * @param JsonApiResource|JsonApiCollectionResponse<mixed>|null $resource
     * @param bool|Closure(T): bool $include
     * @param ?array<mixed> $meta
     * @param ?array<mixed> $links
     */
    public function __construct(JsonApiResource|JsonApiCollectionResponse|null $resource, protected bool|Closure $include = true, protected ?array $meta = null, protected ?array $links = null)
    {
        parent::__construct($resource);
    }

    /**
     * @inheritDoc
     *
     * @return array<string, mixed>|ArrayableContract<string, mixed>|JsonSerializable
     */
    public function toArray(mixed $request): array|ArrayableContract|JsonSerializable
    {
        $data = [
            'data' => $this->getData(),
        ];

        $meta = $this->filter($this->getMeta());

        if (\count($meta) > 0) {
            $data['meta'] = $meta;
        }

        $links = $this->filter($this->getLinks());

        if (\count($links) > 0) {
            $data['links'] = $links;
        }

        return $data;
    }

    /**
     * Get items.
     *
     * @return Collection<int, JsonApiResource>
     */
    public function items(): Collection
    {
        $collection = collect([]);

        if ($this->include === false) {
            return $collection;
        }

        if ($this->resource instanceof JsonApiCollectionResponse) {
            $collection = $this->resource->collection;
        }

        if ($this->resource instanceof JsonApiResource) {
            $collection = collect([$this->resource]);
        }

        if ($this->include === true) {
            return $collection;
        }

        return $collection->filter($this->include);
    }

    /**
     * Get meta.
     *
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta ?? [];
    }

    /**
     * Get links.
     *
     * @return array<string, mixed>
     */
    public function getLinks(): array
    {
        return $this->links ?? [];
    }

    /**
     * Get data.
     *
     * @return ?array<mixed>
     */
    public function getData(): ?array
    {
        if ($this->resource instanceof JsonApiResource) {
            return $this->resource->getHeader();
        }

        if ($this->resource instanceof JsonApiCollectionResponse) {
            return $this->resource->collection->map(static function (JsonApiResource $resource): array {
                return $resource->getHeader();
            })->all();
        }

        return null;
    }
}
