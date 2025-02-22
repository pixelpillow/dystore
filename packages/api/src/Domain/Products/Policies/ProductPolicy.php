<?php

namespace Dystore\Api\Domain\Products\Policies;

use Dystore\Api\Domain\Auth\Concerns\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Lunar\Models\Contracts\Product as ProductContract;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?Authenticatable $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Determine if the given user can create posts.
     */
    public function create(?Authenticatable $user): bool
    {
        if ($this->isFilamentAdmin($user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?Authenticatable $user, ProductContract $product): bool
    {
        if ($this->isFilamentAdmin($user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?Authenticatable $user, ProductContract $product): bool
    {
        if ($this->isFilamentAdmin($user)) {
            return true;
        }

        return false;
    }

    /**
     * Authorize a user to view product's associations.
     */
    public function viewProductAssociations(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's brand.
     */
    public function viewBrand(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's cheapest variant.
     */
    public function viewCheapestProductVariant(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's most expensive variant.
     */
    public function viewMostExpensiveProductVariant(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's collections.
     */
    public function viewCollections(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's default url.
     */
    public function viewDefaultUrl(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's channels.
     */
    public function viewChannels(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's images.
     */
    public function viewImages(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's inverse associations.
     */
    public function viewInverseProductAssociations(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's prices.
     */
    public function viewPrices(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's lowest price.
     */
    public function viewLowestPrice(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's highest price.
     */
    public function viewHighestPrice(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's tags.
     */
    public function viewTags(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's thumbnail.
     */
    public function viewThumbnail(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's urls.
     */
    public function viewUrls(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's variants.
     */
    public function viewProductVariants(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's options.
     */
    public function viewProductOptions(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }

    /**
     * Authorize a user to view product's option values.
     */
    public function viewProductOptionValues(?Authenticatable $user, ProductContract $product): bool
    {
        return true;
    }
}
