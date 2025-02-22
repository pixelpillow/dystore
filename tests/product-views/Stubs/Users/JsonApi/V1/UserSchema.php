<?php

namespace Dystore\Tests\ProductViews\Stubs\Users\JsonApi\V1;

use Dystore\Api\Domain\JsonApi\Eloquent\Schema;
use Dystore\Tests\ProductViews\Stubs\User;
use LaravelJsonApi\Eloquent\Fields\ID;

class UserSchema extends Schema
{
    /**
     * {@inheritDoc}
     */
    public static string $model = User::class;

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            ID::make(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function authorizable(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public static function type(): string
    {
        return 'users';
    }
}
