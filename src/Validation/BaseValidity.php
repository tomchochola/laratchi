<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Tomchochola\Laratchi\Enums\RequestModeEnum;

class BaseValidity
{
    /**
     * Signature validation rules.
     */
    public function signature(): Validity
    {
        return Validity::make()->string(null);
    }

    /**
     * Expires validation rules.
     */
    public function expires(): Validity
    {
        return Validity::make()->integer(null, null);
    }

    /**
     * Cursor validation rules.
     */
    public function cursor(): Validity
    {
        return Validity::make()
            ->string(null)
            ->cursor();
    }

    /**
     * Page validation rules.
     */
    public function page(): Validity
    {
        return Validity::make()->positive(null, null);
    }

    /**
     * Take validation rules.
     */
    public function take(int $max): Validity
    {
        return Validity::make()->positive($max, null);
    }

    /**
     * Filter validation rules.
     */
    public function filter(): Validity
    {
        return Validity::make()->array(null);
    }

    /**
     * Id validation rules.
     */
    public function id(): Validity
    {
        return Validity::make()->positive(null, null);
    }

    /**
     * Slug validation rules.
     */
    public function slug(): Validity
    {
        return Validity::make()->string(null);
    }

    /**
     * Mode validation rules.
     *
     * @param array<int> $modes
     */
    public function mode(array $modes): Validity
    {
        return Validity::make()->inInteger($modes);
    }

    /**
     * Search validation rules.
     */
    public function search(): Validity
    {
        return Validity::make()->varchar();
    }

    /**
     * Sort validation rules.
     *
     * @param array<string> $sort
     */
    public function sort(array $sort): Validity
    {
        return Validity::make()->inString($sort);
    }

    /**
     * Date validation rules.
     */
    public function date(): Validity
    {
        return Validity::make()
            ->string(null)
            ->dateFormat();
    }

    /**
     * Datetime validation rules.
     */
    public function datetime(): Validity
    {
        return Validity::make()
            ->string(null)
            ->date();
    }

    /**
     * Predefined rules.
     *
     * @param array<string>|null $sort
     * @param array<int>|bool $mode
     * @param Closure(): Builder|null $dataId
     *
     * @return array<string, mixed>
     */
    public function predefined(
        bool $signed = false,
        bool $cursor = false,
        bool $page = false,
        int|bool $take = false,
        bool $filter = false,
        bool $id = false,
        bool $slug = false,
        bool $filterId = false,
        bool $filterNotId = false,
        bool $filterSlug = false,
        bool $filterNotSlug = false,
        bool $filterSearch = false,
        bool $idXorSlug = false,
        bool $idOrSlug = false,
        array|null $sort = null,
        array|bool $mode = false,
        Closure|null $dataId = null,
        bool $data = false,
    ): array {
        $rules = [];

        if ($signed) {
            $rules = \array_replace($rules, [
                'signature' => $this->signature()
                    ->nullable()
                    ->filled()
                    ->requiredWith(['expires']),
                'expires' => $this->expires()
                    ->nullable()
                    ->filled(),
            ]);
        }

        if ($cursor) {
            $rules = \array_replace($rules, [
                'cursor' => $this->cursor()
                    ->nullable()
                    ->filled(),
            ]);
        }

        if ($page) {
            $rules = \array_replace($rules, [
                'page' => $this->page()
                    ->nullable()
                    ->filled(),
            ]);
        }

        if ($take !== false) {
            $rules = \array_replace($rules, [
                'take' => $this->take($take === true ? \PHP_INT_MAX : $take)
                    ->nullable()
                    ->filled(),
            ]);
        }

        if ($filter || $filterId || $filterSlug || $filterNotId || $filterNotSlug || $filterSearch) {
            $rules = \array_replace($rules, [
                'filter' => $this->filter()
                    ->nullable()
                    ->filled(),
            ]);
        }

        if ($id) {
            $rules = \array_replace($rules, [
                'id' => $this->id()
                    ->nullable()
                    ->filled(),
            ]);
        }

        if ($slug) {
            $rules = \array_replace($rules, [
                'slug' => $this->slug()
                    ->nullable()
                    ->filled(),
            ]);
        }

        if ($idOrSlug) {
            $rules = \array_replace($rules, [
                'id' => $this->id()
                    ->nullable()
                    ->filled()
                    ->requiredWithout(['slug']),
                'slug' => $this->slug()
                    ->nullable()
                    ->filled()
                    ->requiredWithout(['id']),
            ]);
        }

        if ($idXorSlug) {
            $rules = \array_replace($rules, [
                'id' => $this->id()
                    ->nullable()
                    ->filled()
                    ->missingWith(['slug'])
                    ->requiredWithout(['slug']),
                'slug' => $this->slug()
                    ->nullable()
                    ->filled()
                    ->missingWith(['id'])
                    ->requiredWithout(['id']),
            ]);
        }

        if ($filterId) {
            $rules = \array_replace($rules, [
                'filter.id' => Validity::make()
                    ->collection(null)
                    ->nullable()
                    ->filled(),
                'filter.id.*' => $this->id()
                    ->required()
                    ->distinct(),
            ]);
        }

        if ($filterNotId) {
            $rules = \array_replace($rules, [
                'filter.not_id' => Validity::make()
                    ->collection(null)
                    ->nullable()
                    ->filled(),
                'filter.not_id.*' => $this->id()
                    ->required()
                    ->distinct(),
            ]);
        }

        if ($filterSlug) {
            $rules = \array_replace($rules, [
                'filter.slug' => Validity::make()
                    ->collection(null)
                    ->nullable()
                    ->filled(),
                'filter.slug.*' => $this->slug()
                    ->required()
                    ->distinct(),
            ]);
        }

        if ($filterNotSlug) {
            $rules = \array_replace($rules, [
                'filter.not_slug' => Validity::make()
                    ->collection(null)
                    ->nullable()
                    ->filled(),
                'filter.not_slug.*' => $this->slug()
                    ->required()
                    ->distinct(),
            ]);
        }

        if ($filterSearch) {
            $rules = \array_replace($rules, [
                'filter.search' => $this->search()
                    ->nullable()
                    ->filled(),
            ]);
        }

        if ($sort !== null) {
            $rules = \array_replace($rules, [
                'sort' => Validity::make()
                    ->collection(null)
                    ->nullable()
                    ->filled(),
                'sort.*' => $this->sort($sort)
                    ->required()
                    ->distinct(),
            ]);
        }

        if ($mode !== false) {
            $rules = \array_replace($rules, [
                'mode' => $this->mode($mode === true ? RequestModeEnum::values() : $mode)
                    ->nullable()
                    ->filled(),
            ]);
        }

        if ($data || $dataId !== null) {
            $rules = \array_replace($rules, [
                'data' => Validity::make()
                    ->collection(null)
                    ->nullable()
                    ->filled(),
                'data.*' => Validity::make()
                    ->array(null)
                    ->required(),
            ]);
        }

        if ($dataId !== null) {
            $rules = \array_replace($rules, [
                'data.*.id' => $this->id()
                    ->builderKey($dataId)
                    ->distinct()
                    ->required(),
            ]);
        }

        return $rules;
    }
}
