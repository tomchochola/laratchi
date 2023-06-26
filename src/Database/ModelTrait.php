<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use RuntimeException;
use Tomchochola\Laratchi\Http\JsonApi\JsonApiResource;
use Tomchochola\Laratchi\Http\JsonApi\ModelResource;
use Tomchochola\Laratchi\Http\Requests\FormRequest;

/**
 * @mixin Model
 */
trait ModelTrait
{
    /**
     * ModelTrait constructor.
     *
     * @param array<mixed> $attributes
     */
    final public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Find instance by key.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByKey(int $key, ?Closure $closure = null): ?static
    {
        $builder = static::query()->whereKey($key);

        if ($closure !== null) {
            $builder = $builder->tap($closure);
        }

        $instance = $builder->first();

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
     * @param ?Closure(): never $onError
     */
    public static function mustFindByKey(int $key, ?Closure $closure = null, ?Closure $onError = null): static
    {
        $instance = static::findByKey($key, $closure);

        if ($instance === null) {
            if ($onError !== null) {
                $onError();
            }

            throw new RuntimeException('Instance not found.');
        }

        return $instance;
    }

    /**
     * Find instance by route key.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByRouteKey(string $key, ?Closure $closure = null): ?static
    {
        $qualifier = new static();

        $builder = static::query();

        $builder->getQuery()->where($qualifier->getQualifiedRouteKeyName(), $key);

        if ($closure !== null) {
            $builder = $builder->tap($closure);
        }

        $instance = $builder->first();

        \assert($instance === null || $instance instanceof static);

        return $instance;
    }

    /**
     * Mandatory find instance by route key.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param ?Closure(): never $onError
     */
    public static function mustFindByRouteKey(string $key, ?Closure $closure = null, ?Closure $onError = null): static
    {
        $instance = static::findByRouteKey($key, $closure);

        if ($instance === null) {
            if ($onError !== null) {
                $onError();
            }

            throw new RuntimeException('Instance not found.');
        }

        return $instance;
    }

    /**
     * Resolve model.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function resolve(?int $id = null, ?string $slug = null, ?Closure $closure = null): ?static
    {
        if ($id !== null && $id > 0) {
            $instance = static::findByKey($id, $closure);

            if ($instance !== null) {
                return $instance;
            }
        }

        if ($slug !== null && $slug !== '') {
            return static::findByRouteKey($slug, $closure);
        }

        return null;
    }

    /**
     * Must resolve model.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param ?Closure(): never $onError
     */
    public static function mustResolve(?int $id = null, ?string $slug = null, ?Closure $closure = null, ?Closure $onError = null): static
    {
        if ($id !== null && $id > 0) {
            $instance = static::findByKey($id, $closure);

            if ($instance !== null) {
                return $instance;
            }
        }

        if ($slug !== null && $slug !== '') {
            $instance = static::findByRouteKey($slug, $closure);

            if ($instance !== null) {
                return $instance;
            }
        }

        if ($onError !== null) {
            $onError();
        }

        throw new RuntimeException('Instance not found.');
    }

    /**
     * Resolve from request.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function resolveFromRequest(FormRequest $request, ?Closure $closure = null, ?string $idKey = 'id', ?string $routeKey = 'slug'): ?static
    {
        \assert(! ($idKey === null && $routeKey === null));

        return static::resolve($idKey !== null ? $request->allInput()->int($idKey) : null, $routeKey !== null ? $request->allInput()->string($routeKey) : null, $closure);
    }

    /**
     * Must resolve from request.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function mustResolveFromRequest(FormRequest $request, ?Closure $closure = null, ?string $idKey = 'id', ?string $routeKey = 'slug'): static
    {
        \assert(! ($idKey === null && $routeKey === null));

        return static::mustResolve($idKey !== null ? $request->allInput()->int($idKey) : null, $routeKey !== null ? $request->allInput()->string($routeKey) : null, $closure, static function () use ($request, $idKey, $routeKey): never {
            if ($idKey !== null && $routeKey !== null) {
                $request->throwSingleValidationException([$idKey, $routeKey], 'invalid');
            }

            \assert(! ($idKey === null && $routeKey === null));

            $request->throwSingleValidationException([$idKey ?? $routeKey], 'invalid');
        });
    }

    /**
     * Create new model.
     *
     * @param array<mixed> $attributes
     * @param (Closure(static): void)|null $closure
     */
    public static function create(array $attributes, ?Closure $closure = null): static
    {
        $model = new static($attributes);

        if ($closure !== null) {
            $closure($model);
        }

        $model->save();

        return $model;
    }

    /**
     * Make new model.
     *
     * @param array<mixed> $attributes
     * @param (Closure(static): void)|null $closure
     */
    public static function make(array $attributes, ?Closure $closure = null): static
    {
        $model = new static($attributes);

        if ($closure !== null) {
            $closure($model);
        }

        return $model;
    }

    /**
     * Scope by keys.
     *
     * @param array<mixed> $ids
     */
    public static function scopeKeys(Builder $builder, array $ids): void
    {
        $builder->whereKey($ids);
    }

    /**
     * Scope by not keys.
     *
     * @param array<mixed> $ids
     */
    public static function scopeNotKeys(Builder $builder, array $ids): void
    {
        $builder->whereKeyNot($ids);
    }

    /**
     * Scope by route keys.
     *
     * @param array<mixed> $slugs
     */
    public static function scopeRouteKeys(Builder $builder, array $slugs): void
    {
        $qualifier = new static();

        $builder->getQuery()->whereIn($qualifier->getQualifiedRouteKeyName(), $slugs);
    }

    /**
     * Scope by not route keys.
     *
     * @param array<mixed> $slugs
     */
    public static function scopeNotRouteKeys(Builder $builder, array $slugs): void
    {
        $qualifier = new static();

        $builder->getQuery()->whereNotIn($qualifier->getQualifiedRouteKeyName(), $slugs);
    }

    /**
     * Scope by id/slug.
     *
     * @param array<mixed> $values
     */
    public static function scopeIdSlug(Builder $builder, array $values, ?bool $preferId = null): void
    {
        $qualifier = new static();

        if ($preferId === null) {
            $builder->where(static function (Builder $builder) use ($values, $qualifier): void {
                $builder->whereKey($values)->getQuery()->orWhereIn($qualifier->getQualifiedRouteKeyName(), $values);
            });
        } elseif ($preferId) {
            $builder->whereKey($values);
        } else {
            $builder->getQuery()->whereIn($qualifier->getQualifiedRouteKeyName(), $values);
        }
    }

    /**
     * Scope by not id/slug.
     *
     * @param array<mixed> $values
     */
    public static function scopeNotIdSlug(Builder $builder, array $values, ?bool $preferId = null): void
    {
        $qualifier = new static();

        if ($preferId === null) {
            $builder->where(static function (Builder $builder) use ($values, $qualifier): void {
                $builder->whereKeyNot($values)->getQuery()->whereNotIn($qualifier->getQualifiedRouteKeyName(), $values);
            });
        } elseif ($preferId) {
            $builder->whereKeyNot($values);
        } else {
            $builder->getQuery()->whereNotIn($qualifier->getQualifiedRouteKeyName(), $values);
        }
    }

    /**
     * Find instance by id.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findById(int $key, ?Closure $closure = null): ?static
    {
        return static::findByKey($key, $closure);
    }

    /**
     * Mandatory find instance by key.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param ?Closure(): never $onError
     */
    public static function mustFindById(int $key, ?Closure $closure = null, ?Closure $onError = null): static
    {
        return static::mustFindByKey($key, $closure, $onError);
    }

    /**
     * Find instance by slug.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findBySlug(string $key, ?Closure $closure = null): ?static
    {
        return static::findByRouteKey($key, $closure);
    }

    /**
     * Mandatory find instance by slug.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param ?Closure(): never $onError
     */
    public static function mustFindBySlug(string $key, ?Closure $closure = null, ?Closure $onError = null): static
    {
        return static::mustFindByRouteKey($key, $closure, $onError);
    }

    /**
     * Find instance by id/slug.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByIdSlug(int|string $value, ?Closure $closure = null, ?bool $preferId = null): ?static
    {
        $builder = static::query();

        static::scopeIdSlug($builder, [$value], $preferId);

        if ($closure !== null) {
            $builder = $builder->tap($closure);
        }

        $instance = $builder->first();

        \assert($instance === null || $instance instanceof static);

        return $instance;
    }

    /**
     * Mandatory find instance by id/slug.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param ?Closure(): never $onError
     */
    public static function mustFindByIdSlug(int|string $value, ?Closure $closure = null, ?Closure $onError = null, ?bool $preferId = null): static
    {
        $instance = static::findByIdSlug($value, $closure, $preferId);

        if ($instance === null) {
            if ($onError !== null) {
                $onError();
            }

            throw new RuntimeException('Instance not found.');
        }

        return $instance;
    }

    /**
     * Find all by key.
     *
     * @param array<mixed> $values
     * @param (Closure(Builder): void)|null $closure
     *
     * @return Collection<array-key, static>
     */
    public static function allById(array $values, ?Closure $closure = null): Collection
    {
        $builder = static::query();

        static::scopeId($builder, $values);

        if ($closure !== null) {
            $builder = $builder->tap($closure);
        }

        $instances = $builder->get();

        \assert($instances instanceof Collection);

        return $instances;
    }

    /**
     * Find all by slug.
     *
     * @param array<mixed> $values
     * @param (Closure(Builder): void)|null $closure
     *
     * @return Collection<array-key, static>
     */
    public static function allBySlug(array $values, ?Closure $closure = null): Collection
    {
        $builder = static::query();

        static::scopeSlug($builder, $values);

        if ($closure !== null) {
            $builder = $builder->tap($closure);
        }

        $instances = $builder->get();

        \assert($instances instanceof Collection);

        return $instances;
    }

    /**
     * Find all by id/slug.
     *
     * @param array<mixed> $values
     * @param (Closure(Builder): void)|null $closure
     *
     * @return Collection<array-key, static>
     */
    public static function allByIdSlug(array $values, ?Closure $closure = null, ?bool $preferId = null): Collection
    {
        $builder = static::query();

        static::scopeIdSlug($builder, $values, $preferId);

        if ($closure !== null) {
            $builder = $builder->tap($closure);
        }

        $instances = $builder->get();

        \assert($instances instanceof Collection);

        return $instances;
    }

    /**
     * Find by id or slug.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByIdOrSlug(?int $id = null, ?string $slug = null, ?Closure $closure = null): ?static
    {
        if ($id !== null && $id > 0) {
            $instance = static::findById($id, $closure);

            if ($instance !== null) {
                return $instance;
            }
        }

        if ($slug !== null && $slug !== '') {
            return static::findBySlug($slug, $closure);
        }

        return null;
    }

    /**
     * Must find by id and slug.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param ?Closure(): never $onError
     */
    public static function mustFindByIdAndSlug(?int $id = null, ?string $slug = null, ?Closure $closure = null, ?Closure $onError = null): static
    {
        $instance = static::findByIdAndSlug($id, $slug, $closure);

        if ($instance !== null) {
            return $instance;
        }

        if ($onError !== null) {
            $onError();
        }

        throw new RuntimeException('Instance not found.');
    }

    /**
     * Find by id and slug.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByIdAndSlug(?int $id = null, ?string $slug = null, ?Closure $closure = null): ?static
    {
        if ($id !== null && $id > 0) {
            return static::findById($id, $closure);
        }

        if ($slug !== null && $slug !== '') {
            return static::findBySlug($slug, $closure);
        }

        return null;
    }

    /**
     * Must find by id or slug.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param ?Closure(): never $onError
     */
    public static function mustFindByIdOrSlug(?int $id = null, ?string $slug = null, ?Closure $closure = null, ?Closure $onError = null): static
    {
        $instance = static::findByIdOrSlug($id, $slug, $closure);

        if ($instance !== null) {
            return $instance;
        }

        if ($onError !== null) {
            $onError();
        }

        throw new RuntimeException('Instance not found.');
    }

    /**
     * Scope by id.
     *
     * @param array<mixed> $ids
     */
    public static function scopeId(Builder $builder, array $ids): void
    {
        $builder->whereKey($ids);
    }

    /**
     * Scope by not id.
     *
     * @param array<mixed> $ids
     */
    public static function scopeNotId(Builder $builder, array $ids): void
    {
        $builder->whereKeyNot($ids);
    }

    /**
     * Scope by slug.
     *
     * @param array<mixed> $slugs
     */
    public static function scopeSlug(Builder $builder, array $slugs): void
    {
        $qualifier = new static();

        $builder->getQuery()->whereIn($qualifier->getQualifiedRouteKeyName(), $slugs);
    }

    /**
     * Scope by not slug.
     *
     * @param array<mixed> $slugs
     */
    public static function scopeNotSlug(Builder $builder, array $slugs): void
    {
        $qualifier = new static();

        $builder->getQuery()->whereNotIn($qualifier->getQualifiedRouteKeyName(), $slugs);
    }

    /**
     * Get clean instance.
     *
     * @param Closure(Builder): void $closure
     */
    public function clean(Closure $closure): static
    {
        $query = $this->newQueryWithoutScopes();

        \assert($query instanceof Builder);

        $query->whereKey($this->getKey());

        $query->getQuery()->useWritePdo();

        $closure($query);

        $instance = $query->first();

        \assert($instance instanceof static);

        $instance->wasRecentlyCreated = $this->wasRecentlyCreated;

        return $instance;
    }

    /**
     * Get the table qualified key name.
     */
    public function getQualifiedRouteKeyName(): string
    {
        return $this->qualifyColumn($this->getRouteKeyName());
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

    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey(): string
    {
        \assert($this->attributeLoaded($this->getRouteKeyName()));

        $value = $this->getAttributeValue($this->getRouteKeyName());

        \assert(\is_scalar($value), 'model route key is string');

        return (string) $value;
    }

    /**
     * Determine if the given attribute is loaded.
     */
    public function attributeLoaded(string $key): bool
    {
        return \array_key_exists($key, $this->attributes);
    }

    /**
     * Get ?int attribute.
     */
    public function int(string $key): ?int
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_int($value), "[{$key}] attribute is not int or null");

        return $value;
    }

    /**
     * Get int attribute.
     */
    public function mustInt(string $key): int
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_int($value), "[{$key}] attribute is not int");

        return $value;
    }

    /**
     * Get ?float attribute.
     */
    public function float(string $key): ?float
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_float($value), "[{$key}] attribute is not float or null");

        return $value;
    }

    /**
     * Get float attribute.
     */
    public function mustFloat(string $key): float
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_float($value), "[{$key}] attribute is not float");

        return $value;
    }

    /**
     * Get ?string attribute.
     */
    public function string(string $key): ?string
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_string($value), "[{$key}] attribute is not string or null");

        return $value;
    }

    /**
     * Get string attribute.
     */
    public function mustString(string $key): string
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_string($value), "[{$key}] attribute is not string");

        return $value;
    }

    /**
     * Get ?bool attribute.
     */
    public function bool(string $key): ?bool
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_bool($value), "[{$key}] attribute is not bool or null");

        return $value;
    }

    /**
     * Get bool attribute.
     */
    public function mustBool(string $key): bool
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_bool($value), "[{$key}] attribute is not bool");

        return $value;
    }

    /**
     * Get ?array attribute.
     *
     * @return array<mixed>|null
     */
    public function array(string $key): ?array
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_array($value), "[{$key}] attribute is not array or null");

        return $value;
    }

    /**
     * Get array attribute.
     *
     * @return array<mixed>
     */
    public function mustArray(string $key): array
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_array($value), "[{$key}] attribute is not array");

        return $value;
    }

    /**
     * Get ?object attribute.
     */
    public function object(string $key): ?object
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_object($value), "[{$key}] attribute is not object or null");

        return $value;
    }

    /**
     * Get object attribute.
     */
    public function mustObject(string $key): object
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_object($value), "[{$key}] attribute is not object");

        return $value;
    }

    /**
     * Get ?Carbon attribute.
     */
    public function carbon(string $key): ?Carbon
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || $value instanceof Carbon, "[{$key}] attribute is not Carbon or null");

        return $value;
    }

    /**
     * Get Carbon attribute.
     */
    public function mustCarbon(string $key): Carbon
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value instanceof Carbon, "[{$key}] attribute is not Carbon");

        return $value;
    }

    /**
     * Resolve relationship.
     *
     * @template T of Model
     *
     * @param class-string<T> $type
     *
     * @return ?T
     */
    public function relation(string $key, string $type): ?Model
    {
        \assert($this->relationLoaded($key), "[{$key}] relationship is not loaded");

        $value = $this->getRelationValue($key);

        \assert($value === null || $value instanceof $type, "[{$key}] relationship is not of type [{$type}]");

        return $value;
    }

    /**
     * Must resolve relationship.
     *
     * @template T of Model
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    public function mustRelation(string $key, string $type): Model
    {
        $value = $this->relation($key, $type);

        \assert($value !== null, "[{$key}] relationship is null");

        return $value;
    }

    /**
     * Must resolve relationships.
     *
     * @template T of Model
     *
     * @param class-string<T> $type
     *
     * @return Collection<array-key, T>
     */
    public function mustRelations(string $key, string $type): Collection
    {
        \assert($this->relationLoaded($key), "[{$key}] relationship is not loaded");

        $value = $this->getRelationValue($key);

        \assert($value instanceof Collection, "[{$key}] relationship is not of type [{$type}] and is not collection");

        return $value;
    }

    /**
     * Created at getter.
     */
    public function getCreatedAt(): Carbon
    {
        return $this->mustCarbon('created_at');
    }

    /**
     * Updated at getter.
     */
    public function getUpdatedAt(): Carbon
    {
        return $this->mustCarbon('updated_at');
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $options
     */
    public function save(array $options = []): bool
    {
        $ok = parent::save($options);

        assertNeverIfNot($ok, 'model not saved correctly');

        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool
    {
        assertNeverIfNot($this->exists, 'model not exists');

        $ok = parent::delete();

        assertNeverIfNot($ok === true, 'model not deleted correctly');

        return true;
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $attributes
     * @param array<mixed> $options
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        assertNeverIfNot($this->exists, 'model not exists');

        $this->fill($attributes);

        if ($this->isDirty()) {
            $this->save();
        }

        return true;
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $attributes
     * @param array<mixed> $options
     */
    public function updateQuietly(array $attributes = [], array $options = []): bool
    {
        assertNeverIfNot($this->exists, 'model not exists');

        $this->fill($attributes);

        if ($this->isDirty()) {
            $this->saveQuietly();
        }

        return true;
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $attributes
     * @param array<mixed> $options
     */
    public function updateOrFail(array $attributes = [], array $options = []): bool
    {
        assertNeverIfNot($this->exists, 'model not exists');

        $this->fill($attributes);

        if ($this->isDirty()) {
            $this->saveOrFail();
        }

        return true;
    }

    /**
     * Embed resource.
     */
    public function embedResource(): JsonApiResource
    {
        return new ModelResource($this);
    }
}
