<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Resources;

use Tomchochola\Laratchi\Auth\DatabaseTokenableInterface;
use Tomchochola\Laratchi\Http\Resources\ModelJsonApiResource;

class MeJsonApiResource extends ModelJsonApiResource
{
    /**
     * @inheritDoc
     */
    public function getRelationships(): array
    {
        $relationships = parent::getRelationships();

        if ($this->resource instanceof DatabaseTokenableInterface) {
            $databaseToken = $this->resource->getDatabaseToken();

            if ($databaseToken !== null) {
                $relationships['database_token'] = new DatabaseTokenJsonApiResource($databaseToken);
            }
        }

        return $relationships;
    }
}
