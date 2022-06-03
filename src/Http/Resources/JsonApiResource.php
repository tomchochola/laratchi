<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Resources;

use Illuminate\Contracts\Support\Arrayable as ArrayableContract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\PotentiallyMissing;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class JsonApiResource extends JsonResource
{
    /**
     * Include included.
     *
     * @param iterable<self> $included
     * @param array<string, self> $map
     */
    public static function withIncluded(iterable $included, array &$map, Request $request): void
    {
        foreach ($included as $resource) {
            $map[$resource->getKey().':'.$resource->getType()] = $resource->toArray($request);

            static::withIncluded($resource->getIncluded(), $map, $request);
        }
    }

    /**
     * Get key.
     */
    abstract public function getKey(): string;

    /**
     * Get slug.
     */
    abstract public function getSlug(): string;

    /**
     * Get type.
     */
    abstract public function getType(): string;

    /**
     * Get attributes.
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return [];
    }

    /**
     * Get relationships.
     *
     * @return array<string, self|array<self>|PotentiallyMissing|JsonApiRelationship|null>
     */
    public function getRelationships(): array
    {
        return [];
    }

    /**
     * Get included resources.
     *
     * @return iterable<self>
     */
    public function getIncluded(): iterable
    {
        $relationships = $this->filter($this->getRelationships());

        foreach ($relationships as $relationship) {
            if ($relationship === null) {
                continue;
            }

            $items = [];

            if ($relationship instanceof JsonApiRelationship) {
                $items = $relationship->items()->all();
            } elseif (\is_array($relationship)) {
                $items = $relationship;
            } else {
                $items = [$relationship];
            }

            foreach ($items as $item) {
                \assert($item instanceof self);

                yield $item;
            }
        }
    }

    /**
     * Get meta.
     *
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return [];
    }

    /**
     * Get resource header.
     *
     * @return array{id: string, slug: string, type: string, meta?: array<mixed>}
     */
    public function getHeader(): array
    {
        $header = [
            'id' => $this->getKey(),
            'slug' => $this->getSlug(),
            'type' => $this->getType(),
        ];

        $meta = $this->filter($this->getHeaderMeta());

        if (\count($meta) > 0) {
            $header['meta'] = $meta;
        }

        return $header;
    }

    /**
     * @inheritDoc
     *
     * @return array<string, mixed>|ArrayableContract<string, mixed>|JsonSerializable
     */
    public function toArray(mixed $request): array|ArrayableContract|JsonSerializable
    {
        $data = $this->getHeader();

        $attributes = $this->filter($this->getAttributes());

        if (\count($attributes) > 0) {
            $data['attributes'] = $attributes;
        }

        $meta = $this->filter($this->getMeta());

        if (\count($meta) > 0) {
            $data['meta'] = $meta;
        }

        $relationships = $this->filter($this->getRelationships());

        if (\count($relationships) > 0) {
            foreach ($relationships as $name => $relationship) {
                $data['relationships'][Str::snake($name)] = ['data' => $relationship === null ? null : (\is_array($relationship) ? \array_map(static function (mixed $resource): array {
                    \assert($resource instanceof self);

                    return $resource->getHeader();
                }, $relationship) : $relationship->getHeader())];
            }
        }

        return $data;
    }

    /**
     * @inheritDoc
     *
     * @return array<string, mixed>
     */
    public function with(mixed $request): array
    {
        $included = [];

        static::withIncluded($this->getIncluded(), $included, $request);

        if (\count($included) > 0) {
            $this->with['included'] = \array_values($included);
        }

        return parent::with($request);
    }

    /**
     * Get header meta.
     *
     * @return array<mixed>
     */
    public function getHeaderMeta(): array
    {
        return [];
    }
}
