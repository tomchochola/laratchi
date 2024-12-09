<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Support\Arr;
use Tomchochola\Laratchi\Validation\Validity;

class JsonApiValidator
{
    /**
     * Attributes.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Meta.
     *
     * @var array<string, mixed>
     */
    protected array $meta = [];

    /**
     * Relationships.
     *
     * @var array<string, mixed>
     */
    protected array $relationships = [];

    /**
     * JsonApiValidator constructor.
     *
     * @param ?array<string, mixed> $attributes
     * @param ?array<string, mixed> $meta
     */
    public function __construct(protected string|null $type, array|null $attributes = null, array|null $meta = null)
    {
        if ($attributes !== null) {
            $this->attributes($attributes);
        }

        if ($meta !== null) {
            $this->meta($meta);
        }
    }

    /**
     * Merge attributes rules.
     *
     * @param array<string, mixed> $rules
     *
     * @return $this
     */
    public function attributes(array $rules): static
    {
        $this->attributes = \array_replace($this->attributes, $rules);

        return $this;
    }

    /**
     * Merge meta rules.
     *
     * @param array<string, mixed> $rules
     *
     * @return $this
     */
    public function meta(array $rules): static
    {
        $this->meta = \array_replace($this->meta, $rules);

        return $this;
    }

    /**
     * Merge relationships rules.
     *
     * @param array<string, mixed> $rules
     *
     * @return $this
     */
    public function relationships(array $rules): static
    {
        $this->relationships = \array_replace($this->relationships, $rules);

        return $this;
    }

    /**
     * Add relationship rules.
     *
     * @param ?array<string, mixed> $meta
     * @param ?array<string, mixed> $links
     *
     * @return $this
     */
    public function relationship(string $name, self $rules, bool|null $filled, array|null $meta = null, array|null $links = null): static
    {
        if ($filled === false) {
            return $this->relationships([$name => Validity::make()->missing()]);
        }

        $this->relationships([
            $name => Validity::make()
                ->required()
                ->object(['data']),
        ]);

        if ($filled === null) {
            $this->relationships([
                "{$name}.data" => Validity::make()->nullable()->present()->prohibited(),
            ]);
        } else {
            $this->relationships([
                "{$name}.data" => Validity::make()
                    ->required()
                    ->object(['id', 'type', 'slug']),
            ]);
            $this->relationships(Arr::prependKeysWith($rules->headerRules(), "{$name}.data."));
        }

        if ($meta !== null) {
            $this->relationships([
                "{$name}.meta" => Validity::make()->nullable()->filled()->array(null),
            ]);
            $this->relationships(Arr::prependKeysWith($meta, "{$name}.meta."));
        }

        if ($links !== null) {
            $this->relationships([
                "{$name}.links" => Validity::make()->nullable()->filled()->array(null),
            ]);
            $this->relationships(Arr::prependKeysWith($links, "{$name}.links."));
        }

        return $this;
    }

    /**
     * Add relationship collection rules.
     *
     * @param array<int, self>|self $rules
     * @param ?array<string, mixed> $meta
     * @param ?array<string, mixed> $links
     *
     * @return $this
     */
    public function relationshipCollection(string $name, array|self $rules, bool $filled, array|null $meta = null, array|null $links = null): static
    {
        if ($filled === false) {
            return $this->relationships([$name => Validity::make()->missing()]);
        }

        $this->relationships([
            $name => Validity::make()
                ->required()
                ->object(['data']),
        ]);

        if (\is_array($rules)) {
            $this->relationships([
                "{$name}.data" => Validity::make()->nullable()->present()->collection(null),
            ]);

            foreach ($rules as $index => $rule) {
                $this->relationships([
                    "{$name}.data.{$index}" => Validity::make()
                        ->required()
                        ->object(['id', 'type', 'slug']),
                ]);
                $this->relationships(Arr::prependKeysWith($rule->headerRules(), "{$name}.data.{$index}."));
            }
        } else {
            $this->relationships([
                "{$name}.data" => Validity::make()->nullable()->present()->collection(null),
            ]);

            $this->relationships([
                "{$name}.data.*" => Validity::make()
                    ->required()
                    ->object(['id', 'type', 'slug']),
            ]);
            $this->relationships(Arr::prependKeysWith($rules->headerRules(), "{$name}.data.*."));
        }

        if ($meta !== null) {
            $this->relationships([
                "{$name}.meta" => Validity::make()->nullable()->filled()->array(null),
            ]);
            $this->relationships(Arr::prependKeysWith($meta, "{$name}.meta."));
        }

        if ($links !== null) {
            $this->relationships([
                "{$name}.links" => Validity::make()->nullable()->filled()->array(null),
            ]);
            $this->relationships(Arr::prependKeysWith($links, "{$name}.links."));
        }

        return $this;
    }

    /**
     * Get header rules.
     *
     * @return array<string, mixed>
     */
    public function headerRules(): array
    {
        $rules = [
            'id' => Validity::make()->nullable()->filled()->string(null),
            'type' => Validity::make()
                ->nullable()
                ->filled()
                ->string(null)
                ->if($this->type !== null)
                ->in([$this->type]),
            'slug' => Validity::make()->nullable()->filled()->string(null),
        ];

        if (\count($this->meta) > 0) {
            $rules = \array_replace(
                $rules,
                [
                    'meta' => Validity::make()->nullable()->filled()->array(null),
                ],
                Arr::prependKeysWith($this->meta, 'meta.'),
            );
        }

        return $rules;
    }

    /**
     * Get rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = $this->headerRules();

        if (\count($this->attributes) > 0) {
            $rules = \array_replace(
                $rules,
                [
                    'attributes' => Validity::make()->nullable()->filled()->array(null),
                ],
                Arr::prependKeysWith($this->attributes, 'attributes.'),
            );
        }

        if (\count($this->relationships) > 0) {
            $rules = \array_replace(
                $rules,
                [
                    'relationships' => Validity::make()->nullable()->filled()->array(null),
                ],
                Arr::prependKeysWith($this->relationships, 'relationships.'),
            );
        }

        return $rules;
    }
}
