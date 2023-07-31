<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\JsonApi;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

abstract class JsonApiResource
{
    /**
     * Get id.
     */
    abstract public function id(): string;

    /**
     * Get slug.
     */
    abstract public function slug(): string;

    /**
     * Get type.
     */
    abstract public function type(): string;

    /**
     * Get attributes.
     *
     * @return ?array<string, mixed>
     */
    public function attributes(): ?array
    {
        return null;
    }

    /**
     * Get meta.
     *
     * @return ?array<string, mixed>
     */
    public function meta(): ?array
    {
        return null;
    }

    /**
     * Get relationships.
     *
     * @return ?array<string, JsonApiRelationship>
     */
    public function relationships(): ?array
    {
        return null;
    }

    /**
     * Get header.
     *
     * @return array<string, mixed>
     */
    public function header(): array
    {
        return [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'type' => $this->type(),
        ];
    }

    /**
     * Get data.
     *
     * @param Collection<array-key, mixed> $included
     *
     * @return array<string, mixed>
     */
    public function data(Collection $included): array
    {
        $data = $this->header();

        $attributes = $this->attributes();

        if ($attributes !== null) {
            $data['attributes'] = $attributes;
        }

        $meta = $this->meta();

        if ($meta !== null) {
            $data['meta'] = $meta;
        }

        $relationships = $this->relationships();

        if ($relationships !== null) {
            $data['relationships'] = $this->encodeRelationships($relationships, $included);
        }

        return $data;
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

        $data = ['data' => $this->data($included)];

        if ($included->isNotEmpty()) {
            $data['included'] = $included->values()->all();
        }

        if (\count($meta) > 0) {
            $data = \array_replace($data, ['meta' => $meta]);
        }

        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Include relationships.
     *
     * @param array<string, JsonApiRelationship> $relationships
     * @param Collection<array-key, mixed> $included
     *
     * @return ?array<string, mixed>
     */
    public function encodeRelationships(array $relationships, Collection $included): ?array
    {
        $data = [];

        foreach ($relationships as $name => $relationship) {
            if ($relationship->resource === null) {
                $new = null;
            } elseif ($relationship->resource instanceof Collection) {
                $new = $relationship->resource
                    ->map(function (mixed $item) use ($relationship, $included): array {
                        $resource = ($relationship->closureMap)($item);

                        $this->include($resource, $included);

                        return $resource->header();
                    })
                    ->values()
                    ->all();
            } else {
                $resource = ($relationship->closureMap)($relationship->resource);

                $this->include($resource, $included);

                $new = $resource->header();
            }

            $data[$name] = ['data' => $new];

            $meta = $relationship->meta();

            if ($meta !== null) {
                $data[$name]['meta'] = $meta;
            }
        }

        return $data;
    }

    /**
     * Include resource.
     *
     * @param Collection<array-key, mixed> $included
     */
    public function include(self $resource, Collection $included): void
    {
        $key = "{$resource->type()}:{$resource->id()}";

        if ($included->has($key)) {
            return;
        }

        $attributes = $resource->attributes();
        $meta = $resource->meta();
        $relationships = $resource->relationships();

        if ($attributes === null && $meta === null && $relationships === null) {
            return;
        }

        $included[$key] = [];

        $data = $resource->header();

        if ($attributes !== null) {
            $data['attributes'] = $attributes;
        }

        if ($meta !== null) {
            $data['meta'] = $meta;
        }

        if ($relationships !== null) {
            $data['relationships'] = $this->encodeRelationships($relationships, $included);
        }

        $included[$key] = $data;
    }
}
