<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @mixin Model
 */
trait IntModelTrait
{
    /**
     * Find instance by key.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByKey(int $key, ?Closure $closure = null): ?static
    {
        $builder = static::query();

        if ($closure !== null) {
            $builder = $builder->tap($closure);
        }

        $instance = $builder->find($key);

        if ($instance === null) {
            return null;
        }

        \assert($instance instanceof static);

        return $instance;
    }

    /**
     * Mandatory find instance by key.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param (Closure(): never)|null $onError
     */
    public static function mustFindByKey(int $key, ?Closure $closure, ?Closure $onError): static
    {
        $instance = static::findByKey($key, $closure);

        if ($instance === null) {
            if ($onError !== null) {
                $onError();
            }

            throw (new ModelNotFoundException())->setModel(static::class, $key);
        }

        return $instance;
    }

    /**
     * Get the value of the model's primary key.
     */
    public function getKey(): int
    {
        \assert($this->attributeLoaded($this->getKeyName()));

        $value = $this->getAttributeValue($this->getKeyName());

        \assert(\is_int($value), 'model key is not int');

        return $value;
    }
}
