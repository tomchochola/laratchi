<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Exceptions\Panicker;
use Tomchochola\Laratchi\Http\JsonApi\JsonApiResource;
use Tomchochola\Laratchi\Http\JsonApi\ModelResource;
use Tomchochola\Laratchi\Http\Requests\FormRequest;
use Tomchochola\Laratchi\Support\AssertTrait;
use Tomchochola\Laratchi\Support\ParserTrait;
use Tomchochola\Laratchi\Support\Typer;

/**
 * @mixin Model
 */
trait ModelTrait
{
    use AssertTrait;
    use ParserTrait;

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

        return Typer::assertNullableInstance($builder->first(), static::class);
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

            Panicker::panic(__METHOD__, 'model not found', \compact('key'));
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

        return Typer::assertNullableInstance($builder->first(), static::class);
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

            Panicker::panic(__METHOD__, 'model not found', \compact('key'));
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

        Panicker::panic(__METHOD__, 'model not found', \compact('id', 'slug'));
    }

    /**
     * Resolve from request.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function resolveFromRequest(FormRequest $request, ?Closure $closure = null, ?string $idKey = 'id', ?string $routeKey = 'slug'): ?static
    {
        return static::resolve($idKey !== null ? $request->allInput()->int($idKey) : null, $routeKey !== null ? $request->allInput()->string($routeKey) : null, $closure);
    }

    /**
     * Must resolve from request.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function mustResolveFromRequest(FormRequest $request, ?Closure $closure = null, ?string $idKey = 'id', ?string $routeKey = 'slug'): static
    {
        return static::mustResolve(
            $idKey !== null ? $request->allInput()->int($idKey) : null,
            $routeKey !== null ? $request->allInput()->string($routeKey) : null,
            $closure,
            static function () use ($request, $idKey, $routeKey): never {
                if ($idKey !== null && $routeKey !== null) {
                    $request->throwSingleValidationException([$idKey, $routeKey], 'invalid');
                }

                $request->throwSingleValidationException([$idKey ?? ($routeKey ?? '')], 'invalid');
            },
        );
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
                $builder
                    ->whereKey($values)
                    ->getQuery()
                    ->orWhereIn($qualifier->getQualifiedRouteKeyName(), $values);
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
                $builder
                    ->whereKeyNot($values)
                    ->getQuery()
                    ->whereNotIn($qualifier->getQualifiedRouteKeyName(), $values);
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

        return Typer::assertNullableInstance($builder->first(), static::class);
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

            Panicker::panic(__METHOD__, 'model not found', \compact('value'));
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

        return Typer::assertInstance($builder->get(), Collection::class);
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

        return Typer::assertInstance($builder->get(), Collection::class);
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

        return Typer::assertInstance($builder->get(), Collection::class);
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

        Panicker::panic(__METHOD__, 'model not found', \compact('id', 'slug'));
    }

    /**
     * Find by id xor slug.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByIdXorSlug(?int $id = null, ?string $slug = null, ?Closure $closure = null): ?static
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
     * Must find by id xor slug.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param ?Closure(): never $onError
     */
    public static function mustFindByIdXorSlug(?int $id = null, ?string $slug = null, ?Closure $closure = null, ?Closure $onError = null): static
    {
        $instance = static::findByIdXorSlug($id, $slug, $closure);

        if ($instance !== null) {
            return $instance;
        }

        if ($onError !== null) {
            $onError();
        }

        Panicker::panic(__METHOD__, 'model not found', \compact('id', 'slug'));
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
     * Scope in.
     *
     * @param array<mixed> $values
     */
    public static function scopeIn(Builder $builder, string $column, array $values): void
    {
        $builder->getQuery()->whereIn($builder->qualifyColumn($column), $values);
    }

    /**
     * Scope by not in.
     *
     * @param array<mixed> $values
     */
    public static function scopeNotIn(Builder $builder, string $column, array $values): void
    {
        $builder->getQuery()->whereNotIn($builder->qualifyColumn($column), $values);
    }

    /**
     * Scope has.
     *
     * @param ?Closure(Builder): void $closure
     */
    public static function scopeHas(Builder $builder, string $relation, ?Closure $closure = null, string $operator = '>=', int $count = 1): void
    {
        $builder->whereHas($relation, $closure, $operator, $count);
    }

    /**
     * Scope not has.
     *
     * @param ?Closure(Builder): void $closure
     */
    public static function scopeNotHas(Builder $builder, string $relation, ?Closure $closure = null): void
    {
        $builder->whereDoesntHave($relation, $closure);
    }

    /**
     * Scope has id.
     *
     * @param array<mixed> $values
     * @param ?Closure(Builder): void $closure
     */
    public static function scopeHasId(Builder $builder, string $relation, array $values, ?Closure $closure = null, string $operator = '>=', int $count = 1): void
    {
        $builder->whereHas(
            $relation,
            static function (Builder $builder) use ($values, $closure): void {
                $builder->whereKey($values);

                if ($closure !== null) {
                    $closure($builder);
                }
            },
            $operator,
            $count,
        );
    }

    /**
     * Scope not has.
     *
     * @param array<mixed> $values
     * @param ?Closure(Builder): void $closure
     */
    public static function scopeNotHasId(Builder $builder, string $relation, array $values, ?Closure $closure = null): void
    {
        $builder->whereDoesntHave($relation, static function (Builder $builder) use ($values, $closure): void {
            $builder->whereKey($values);

            if ($closure !== null) {
                $closure($builder);
            }
        });
    }

    /**
     * Scope by key.
     *
     * @param array<mixed> $keys
     */
    public static function scopeKey(Builder $builder, array $keys): void
    {
        $builder->whereKey($keys);
    }

    /**
     * Scope by not key.
     *
     * @param array<mixed> $keys
     */
    public static function scopeNotKey(Builder $builder, array $keys): void
    {
        $builder->whereKeyNot($keys);
    }

    /**
     * Scope by route key.
     *
     * @param array<mixed> $routeKeys
     */
    public static function scopeRouteKey(Builder $builder, array $routeKeys): void
    {
        $qualifier = new static();

        $builder->getQuery()->whereIn($qualifier->getQualifiedRouteKeyName(), $routeKeys);
    }

    /**
     * Scope by not route key.
     *
     * @param array<mixed> $routeKeys
     */
    public static function scopeNotRouteKey(Builder $builder, array $routeKeys): void
    {
        $qualifier = new static();

        $builder->getQuery()->whereNotIn($qualifier->getQualifiedRouteKeyName(), $routeKeys);
    }

    /**
     * Find by key xor route key.
     *
     * @param Closure(Builder): void|null $closure
     */
    public static function findByKeyXorRouteKey(?int $key = null, ?string $routeKey = null, ?Closure $closure = null): ?static
    {
        if ($key !== null) {
            return static::findByKey($key, $closure);
        }

        if ($routeKey !== null) {
            return static::findByRouteKey($routeKey, $closure);
        }

        return null;
    }

    /**
     * Must find by key xor route key.
     *
     * @param Closure(Builder): void|null $closure
     * @param Closure(): never|null $onError
     */
    public static function mustFindByKeyXorRouteKey(?int $key = null, ?string $routeKey = null, ?Closure $closure = null, ?Closure $onError = null): static
    {
        $instance = static::findByIdXorSlug($key, $routeKey, $closure);

        if ($instance !== null) {
            return $instance;
        }

        if ($onError !== null) {
            $onError();
        }

        Panicker::panic(__METHOD__, 'model not found', \compact('key', 'routeKey'));
    }

    /**
     * Get clean instance.
     *
     * @param Closure(Builder): void $closure
     */
    public function clean(Closure $closure): static
    {
        $query = Typer::assertInstance($this->newQueryWithoutScopes(), Builder::class);

        $query->whereKey($this->getKey());

        $query->getQuery()->useWritePdo();

        $closure($query);

        $instance = Typer::assertInstance($query->first(), static::class);

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
        return $this->assertInt($this->getKeyName());
    }

    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey(): string
    {
        return (string) $this->assertScalar($this->getRouteKeyName());
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
        return $this->assertNullableInt($key);
    }

    /**
     * Get int attribute.
     */
    public function mustInt(string $key): int
    {
        return $this->assertInt($key);
    }

    /**
     * Get ?float attribute.
     */
    public function float(string $key): ?float
    {
        return $this->assertNullableFloat($key);
    }

    /**
     * Get float attribute.
     */
    public function mustFloat(string $key): float
    {
        return $this->assertFloat($key);
    }

    /**
     * Get ?string attribute.
     */
    public function string(string $key): ?string
    {
        return $this->assertNullableString($key);
    }

    /**
     * Get string attribute.
     */
    public function mustString(string $key): string
    {
        return $this->assertString($key);
    }

    /**
     * Get ?bool attribute.
     */
    public function bool(string $key): ?bool
    {
        return $this->assertNullableBool($key);
    }

    /**
     * Get bool attribute.
     */
    public function mustBool(string $key): bool
    {
        return $this->assertBool($key);
    }

    /**
     * Get ?array attribute.
     *
     * @return array<mixed>|null
     */
    public function array(string $key): ?array
    {
        return $this->assertNullableArray($key);
    }

    /**
     * Get array attribute.
     *
     * @return array<mixed>
     */
    public function mustArray(string $key): array
    {
        return $this->assertArray($key);
    }

    /**
     * Get ?object attribute.
     */
    public function object(string $key): ?object
    {
        return $this->assertNullableObject($key);
    }

    /**
     * Get object attribute.
     */
    public function mustObject(string $key): object
    {
        return $this->assertObject($key);
    }

    /**
     * Get ?Carbon attribute.
     */
    public function carbon(string $key): ?Carbon
    {
        return $this->assertNullableCarbon($key);
    }

    /**
     * Get Carbon attribute.
     */
    public function mustCarbon(string $key): Carbon
    {
        return $this->assertCarbon($key);
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
        return $this->assertNullableRelation($key, $type);
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
        return $this->assertRelationship($key, $type);
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
        return $this->assertRelationshipCollection($key, $type);
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

    /**
     * Mixed getter.
     */
    public function mixed(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->getAttributes();
        }

        return $this->loadedAttribute($key);
    }

    /**
     * Loaded attribute getter.
     */
    public function loadedAttribute(string $key): mixed
    {
        if ($this->attributeLoaded($key)) {
            return $this->getAttributeValue($key);
        }

        Panicker::panic(__METHOD__, 'attribute not loaded', \compact('key'));
    }

    /**
     * Loaded relationship getter.
     */
    public function loadedRelationship(string $key): mixed
    {
        if ($this->relationLoaded($key)) {
            return $this->getRelationValue($key);
        }

        Panicker::panic(__METHOD__, 'relationship not loaded', \compact('key'));
    }

    /**
     * Assert nullable relationship.
     *
     * @template T of Model
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    public function assertNullableRelation(string $key, string $class): ?Model
    {
        return assertNullableInstance($this->loadedRelationship($key), $class);
    }

    /**
     * Assert relationship.
     *
     * @template T of Model
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function assertRelationship(string $key, string $class): Model
    {
        return assertInstance($this->loadedRelationship($key), $class);
    }

    /**
     * Assert relationship collection.
     *
     * @template T of Model
     *
     * @param class-string<T> $class
     *
     * @return Collection<array-key, T>
     */
    public function assertRelationshipCollection(string $key, string $class): Collection
    {
        return assertInstance($this->loadedRelationship($key), Collection::class);
    }

    /**
     * @inheritDoc
     */
    protected function asDateTime(mixed $value): Carbon
    {
        return parent::asDateTime($value)->setTimezone((new Config())->appTimezone());
    }
}
