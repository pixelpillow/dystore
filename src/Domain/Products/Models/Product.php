<?php

namespace Dystcz\LunarApi\Domain\Products\Models;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Lunar\Models\Price;
use Lunar\Models\Product as LunarProduct;
use Lunar\Models\ProductVariant;

class Product extends LunarProduct
{
    /**
     * Get prices through variants.
     *
     * @return HasManyThrough
     */
    public function prices(): HasManyThrough
    {
        return $this
            ->hasManyThrough(
                Price::class,
                ProductVariant::class,
                'product_id',
                'priceable_id'
            )
            ->where(
                'priceable_type',
                ProductVariant::class
            );
    }

    public function lowestPrice(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        $pricesTable = $this->prices()->getModel()->getTable();
        $variantsTable = $this->variants()->getModel()->getTable();

        return $this
            ->hasOneThrough(
                Price::class,
                ProductVariant::class,
                'product_id',
                'priceable_id'
            )
            ->where($pricesTable.'.id', function ($query) use ($variantsTable, $pricesTable) {
                $query->select($pricesTable.'.id')
                    ->from($pricesTable)
                    ->where('priceable_type', ProductVariant::class)
                    ->whereIn('priceable_id', function ($query) use ($variantsTable) {
                        $query->select('variants.id')
                            ->from($variantsTable.' as variants')
                            ->whereRaw('variants.product_id = '.$variantsTable.'.product_id');
                    })
                    ->orderBy($pricesTable.'.price', 'asc')
                    ->limit(1);
            });
    }

    public function cheapestVariant(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        $pricesTable = $this->prices()->getModel()->getTable();
        $variantsTable = $this->variants()->getModel()->getTable();

        return $this
            ->hasOne(ProductVariant::class)
            ->where($variantsTable.'.id', function ($query) use ($variantsTable, $pricesTable) {
                $query
                    ->select('variants.id')
                    ->from($variantsTable.' as variants')
                    ->join($pricesTable, function ($join) {
                        $join->on('priceable_id', '=', 'variants.id')
                            ->where('priceable_type', ProductVariant::class);
                    })
                    ->whereRaw('variants.product_id = lunar_product_variants.product_id')
                    ->orderBy($pricesTable.'.price', 'asc')
                    ->limit(1);
            });
    }

    /**
     * Get base prices through variants.
     *
     * @return HasManyThrough
     */
    public function basePrices(): HasManyThrough
    {
        return $this->prices()->whereTier(1)->whereNull('customer_group_id');
    }
}
