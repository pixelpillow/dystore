<?php

namespace Dystore\Api\Domain\ProductVariants\JsonApi\V1;

use Dystore\Api\Domain\JsonApi\Eloquent\Fields\AttributeData;
use Dystore\Api\Domain\JsonApi\Eloquent\Schema;
use Dystore\Api\Support\Models\Actions\SchemaType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use LaravelJsonApi\Eloquent\Fields\Map;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Fields\Relations\HasOne;
use LaravelJsonApi\Eloquent\Fields\Relations\HasOneThrough;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\WhereHas;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WhereIdNotIn;
use LaravelJsonApi\Eloquent\Resources\Relation;
use Lunar\Models\Contracts\Attribute;
use Lunar\Models\Contracts\Price;
use Lunar\Models\Contracts\ProductOptionValue;
use Lunar\Models\Contracts\ProductVariant;
use Lunar\Models\Contracts\Url;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariantSchema extends Schema
{
    /**
     * {@inheritDoc}
     */
    public static string $model = ProductVariant::class;

    /**
     * Build an index query for this resource.
     */
    public function indexQuery(?Request $request, Builder $query): Builder
    {
        return $query->whereHas(
            'product',
            fn ($query) => $query->where('status', '!=', 'draft'),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function with(): array
    {
        return [
            'attributes',
            'attributes.attributeGroup',
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
            'values',

            'product_option_values',
            'product_option_values.images',
            'product_option_values.product_option',

            ...parent::includePaths(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            $this->idField(),

            AttributeData::make('attribute_data')
                ->groupAttributes(),

            Str::make('sku'),
            Str::make('gtin'),
            Str::make('mpn'),
            Str::make('ean'),
            Str::make('tax_ref'),

            Map::make('availability', [
                ArrayHash::make('stock')
                    ->extractUsing(
                        static fn (ProductVariant $model) => [
                            'quantity_string' => $model->approximateInStockQuantity,
                        ],
                    ),
                ArrayHash::make('status')
                    ->extractUsing(
                        static fn (ProductVariant $model) => $model->availability->toArray()
                    ),
            ]),

            BelongsTo::make('product')
                ->readOnly(),

            HasMany::make('attributes', 'attributes')
                ->type(SchemaType::get(Attribute::class))
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('other_product_variants', 'otherVariants')
                ->type(SchemaType::get(ProductVariant::class))
                ->retainFieldName()
                ->canCount()
                ->countAs('other_product_variants_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('prices')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasOne::make('lowest_price', 'lowestPrice')
                ->type(SchemaType::get(Price::class))
                ->retainFieldName()
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasOne::make('highest_price', 'highestPrice')
                ->type(SchemaType::get(Price::class))
                ->retainFieldName()
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('images', 'images')
                ->type(SchemaType::get(Media::class))
                ->canCount()
                ->countAs('images_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('product_option_values', 'values')
                ->retainFieldName()
                ->type(SchemaType::get(ProductOptionValue::class))
                ->readOnly()
                ->canCount()
                ->countAs('product_option_values_count')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasOne::make('default_url', 'defaultUrl')
                ->type(SchemaType::get(Url::class))
                ->retainFieldName()
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasMany::make('urls')
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            HasOneThrough::make('thumbnail')
                ->type(SchemaType::get(Media::class))
                ->serializeUsing(static fn (Relation $relation) => $relation->withoutLinks()),

            ...parent::fields(),
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

            WhereHas::make($this, 'urls', 'url')
                ->singular(),

            WhereHas::make($this, 'urls', 'urls'),

            ...parent::filters(),
        ];
    }
}
