<?php

namespace Dystore\Api\Domain\ProductTypes\Models;

use Dystore\Api\Domain\ProductTypes\Concerns\InteractsWithDystoreApi;
use Dystore\Api\Domain\ProductTypes\Contracts\ProductType as ProductTypeContract;
use Lunar\Models\ProductType as LunarProductType;

class ProductType extends LunarProductType implements ProductTypeContract
{
    use InteractsWithDystoreApi;
}
