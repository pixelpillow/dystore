<?php

namespace Dystore\Api\Domain\Products\JsonApi\V1;

use Dystore\Api\Domain\JsonApi\Eloquent\Fields\AttributeData;
use Dystore\Api\Domain\JsonApi\Eloquent\Schema;
use Dystore\Api\Domain\JsonApi\Eloquent\Sorts\InDefaultOrder;
use Dystore\Api\Domain\JsonApi\Eloquent\Sorts\InRandomOrder;
use Dystore\Api\Domain\Products\JsonApi\Filters\InStockFilter;
use Dystore\Api\Domain\Products\JsonApi\Filters\ProductFilterCollection;
use Dystore\Api\Support\Models\Actions\SchemaType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use LaravelJsonApi\Eloquent\Fields\Map;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Fields\Relations\HasManyThrough;
use LaravelJsonApi\Eloquent\Fields\Relations\HasOne;
use LaravelJsonApi\Eloquent\Fields\Relations\HasOneThrough;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\WhereHas;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WhereIdNotIn;
use LaravelJsonApi\Eloquent\Resources\Relation;
use Lunar\Models\Contracts\Attribute;
use Lunar\Models\Contracts\Brand;
use Lunar\Models\Contracts\Price;
use Lunar\Models\Contracts\Product;
use Lunar\Models\Contracts\ProductAssociation;
use Lunar\Models\Contracts\ProductOptionValue;
use Lunar\Models\Contracts\ProductType;
use Lunar\Models\Contracts\ProductVariant;
use Lunar\Models\Contracts\Url;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductSchema extends Schema
{
    /**
     * {@inheritDoc}
     */
    public static string $model = Product::class;

    /**
     * {@inheritDoc}
     */
    protected $defaultSort = 'ordered';

    /**
     * Build an index query for this resource.
     */
    public function indexQuery(?Request $request, Builder $query): Builder
    {
        return $query->where('status', '!=', 'draft');
    }

    /**
     * {@inheritDoc}
     */
    public function with(): array
    {
        return [
            'productType',
            'productType.mappedAttributes',
            'productType.mappedAttributes.attributeGroup',

            ...parent::with(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function includePaths(): iterable
    {
        return [
            'default_url',
            'images',
            'lowest_price',
            'prices',
            'thumbnail',
            'urls',

            'brand',
            'brand.default_url',
            'brand.thumbnail',

            'cheapest_product_variant',
            'cheapest_product_variant.images',
            'cheapest_product_variant.prices',

            'collections',
            'collections.default_url',
            'collections.group',

            'product_option.product_option_values',
            'product_option.product_option_values.images',

            'product_option_values',
            'product_option_values.images',
            'product_option_values.product_option',

            'product_type',
            'tags',

            ...parent::includePaths(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function fields(): iterable
    {
        return [
            $this->idField(),

            AttributeData::make('attribute_data')
                ->groupAttributes(),

            Map::make('availability', [
                ArrayHash::make('status')
                    ->extractUsing(
                        static fn (Product $model) => $model->availability->toArray()
                    ),
            ]),

            Str::make('status'),

            HasMany::make('attributes', 'attributes')
                ->type(SchemaType::get(Attribute::class))
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('product_associations', 'associations')
                ->type(SchemaType::get(ProductAssociation::class))
                ->retainFieldName()
                ->canCount()
                ->countAs('product_associations_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            BelongsTo::make('brand')
                ->type(SchemaType::get(Brand::class))
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('channels')
                ->canCount()
                ->countAs('channels_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasOne::make('cheapest_product_variant', 'cheapestVariant')
                ->type(SchemaType::get(ProductVariant::class))
                ->retainFieldName()
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasOne::make('most_expensive_product_variant', 'mostExpensiveVariant')
                ->type(SchemaType::get(ProductVariant::class))
                ->retainFieldName()
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('collections')
                ->canCount()
                ->countAs('collections_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasOne::make('default_url', 'defaultUrl')
                ->type(SchemaType::get(Url::class))
                ->retainFieldName()
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('images', 'images')
                ->type(SchemaType::get(Media::class))
                ->canCount()
                ->countAs('images_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('inverse_product_associations', 'inverseAssociations')
                ->type(SchemaType::get(ProductAssociation::class))
                ->retainFieldName()
                ->canCount()
                ->countAs('inverse_associations_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasOneThrough::make('lowest_price', 'lowestPrice')
                ->type(SchemaType::get(Price::class))
                ->retainFieldName()
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasOneThrough::make('highest_price', 'highestPrice')
                ->type(SchemaType::get(Price::class))
                ->retainFieldName()
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasManyThrough::make('prices')
                ->canCount()
                ->countAs('prices_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            BelongsTo::make('product_type', 'productType')
                ->retainFieldName()
                ->type(SchemaType::get(ProductType::class))
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('tags')
                ->canCount()
                ->countAs('tags_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasOne::make('thumbnail', 'thumbnail')
                ->type(SchemaType::get(Media::class))
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('urls')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('product_variants', 'variants')
                ->type(SchemaType::get(ProductVariant::class))
                ->retainFieldName()
                ->canCount()
                ->countAs('product_variants_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('product_options', 'productOptions')
                ->retainFieldName()
                ->type('product_options')
                ->canCount()
                ->countAs('product_options_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('product_option_values', 'variantValues')
                ->retainFieldName()
                ->type(SchemaType::get(ProductOptionValue::class))
                ->readOnly()
                ->canCount()
                ->countAs('product_option_values_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            ...parent::fields(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function sortables(): iterable
    {
        return [
            ...parent::sortables(),

            InDefaultOrder::make('ordered'),

            InRandomOrder::make('random'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this),

            WhereIdNotIn::make($this),

            InStockFilter::make('in_stock'),

            WhereHas::make($this, 'prices'),

            WhereHas::make($this, 'brand'),

            WhereHas::make($this, 'urls', 'url')
                ->singular(),

            WhereHas::make($this, 'urls', 'urls'),

            WhereHas::make($this, 'product_type'),

            WhereHas::make($this, 'channels'),

            WhereHas::make($this, 'status'),

            WhereHas::make($this, 'collections'),

            ...(new ProductFilterCollection)->toArray(),

            ...parent::filters(),
        ];
    }
}
