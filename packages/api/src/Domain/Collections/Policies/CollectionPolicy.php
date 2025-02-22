<?php

namespace Dystore\Api\Domain\Collections\Policies;

use Dystore\Api\Domain\Auth\Concerns\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Lunar\Models\Contracts\Collection as CollectionContract;

class CollectionPolicy
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
    public function view(?Authenticatable $user, CollectionContract $collection): bool
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
    public function update(?Authenticatable $user, CollectionContract $collection): bool
    {
        if ($this->isFilamentAdmin($user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?Authenticatable $user, CollectionContract $collection): bool
    {
        if ($this->isFilamentAdmin($user)) {
            return true;
        }

        return false;
    }

    /**
     * Authorize a user to view collection's default url.
     */
    public function viewDefaultUrl(?Authenticatable $user, CollectionContract $collection): bool
    {
        return true;
    }

    /**
     * Authorize a user to view collections's images.
     */
    public function viewImages(?Authenticatable $user, CollectionContract $collection): bool
    {
        return true;
    }

    /**
     * Authorize a user to view collections's products.
     */
    public function viewProducts(?Authenticatable $user, CollectionContract $collection): bool
    {
        return true;
    }
}
