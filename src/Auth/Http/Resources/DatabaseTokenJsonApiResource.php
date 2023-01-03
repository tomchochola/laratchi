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
        return [
            'provider' => $this->resource->getProvider(),
            'auth_id' => $this->resource->getAuthId(),
            'bearer' => $this->resource->bearer === '' ? null : $this->resource->bearer,
        ];
    }
}
