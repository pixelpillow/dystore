<?php

namespace Dystcz\LunarApi\Domain\Collections\JsonApi\V1;

use Dystcz\LunarApi\Domain\JsonApi\Eloquent\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Fields\Relations\HasOne;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\WhereHas;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use Lunar\Models\Collection;

class CollectionSchema extends Schema
{
    /**
     * The default paging parameters to use if the client supplies none.
     */
    protected ?array $defaultPagination = ['number' => 1];

    /**
     * The model the schema corresponds to.
     */
    public static string $model = Collection::class;

    /**
     * Build an index query for this resource.
     */
    public function indexQuery(?Request $request, Builder $query): Builder
    {
        return $query;
    }

    /**
     * Build a "relatable" query for this resource.
     */
    public function relatableQuery(?Request $request, Relation $query): Relation
    {
        return $query;
    }

    /**
     * The relationships that should always be eager loaded.
     */
    public function with(): array
    {
        return [
            ...parent::with(),
            'defaultUrl',
        ];
    }

    /**
     * Get the include paths supported by this resource.
     *
     * @return string[]|iterable
     */
    public function includePaths(): iterable
    {
        return [
            ...parent::includePaths(),
            'products',
            'products.urls',
            'products.default_url',
            'products.images',
            'urls',
            'default_url',
        ];
    }

    /**
     * Get the resource fields.
     */
    public function fields(): array
    {
        return [
            ...parent::fields(),

            ID::make(),

            Str::make('name'),

            HasOne::make('default_url', 'defaultUrl')
                ->retainFieldName(),

            HasOne::make('group'),

            HasMany::make('products'),

            HasMany::make('urls'),
        ];
    }

    /**
     * Get the resource filters.
     */
    public function filters(): array
    {
        return [
            ...parent::filters(),

            WhereIdIn::make($this),

            WhereHas::make($this, 'default_urls', 'url')->singular(),
        ];
    }

    /**
     * Will the set of filters result in zero-to-one resource?
     *
     * While individual filters can be marked as singular, there may be instances
     * where the combination of filters should result in a singular response
     * (zero-to-one resource instead of zero-to-many). Developers can use this
     * hook to add complex logic for working out if a set of filters should
     * return a singular resource.
     */
    public function isSingular(array $filters): bool
    {
        // return isset($filters['userId'], $filters['clientId']);

        return false;
    }

    /**
     * Get the resource paginator.
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make()
            ->withDefaultPerPage(Config::get('lunar-api.domains.collections.pagination', 12));
    }

    /**
     * Determine if the resource is authorizable.
     */
    public function authorizable(): bool
    {
        return false; // TODO create policies
    }

    /**
     * Get the JSON:API resource type.
     */
    public static function type(): string
    {
        return 'collections';
    }
}
