<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Resources;

use Tomchochola\Laratchi\Auth\DatabaseToken;
use Tomchochola\Laratchi\Http\Resources\ModelJsonApiResource;

/**
 * @property DatabaseToken $resource
 */
class DatabaseTokenJsonApiResource extends ModelJsonApiResource
{
    /**
     * @inheritDoc
     */
    public function __construct(DatabaseToken $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return \array_merge(parent::getAttributes(), [
            'bearer' => $this->when($this->resource->bearer !== '', $this->resource->bearer),
        ]);
    }
}
