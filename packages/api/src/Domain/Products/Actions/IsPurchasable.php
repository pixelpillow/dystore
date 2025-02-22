<?php

namespace Dystore\Api\Domain\Products\Actions;

use Dystore\Api\Domain\Products\Enums\Availability;
use Dystore\Api\Domain\Products\Models\Product;
use Illuminate\Support\Facades\Cache;
use Lunar\Models\Contracts\Product as ProductContract;

class IsPurchasable
{
    /**
     * Determine if at least one variant of the product is available.
     */
    public function __invoke(ProductContract $product, bool $skipCache = false): bool
    {
        if ($skipCache) {
            return Availability::of($product)->purchasable();
        }

        return Cache::remember(
            "product-{$product->id}-purchasable",
            3600,
            fn () => Availability::of($product)->purchasable(),
        );
    }
}
