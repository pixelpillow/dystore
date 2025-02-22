<?php

namespace Dystore\Api\Domain\Addresses\Http\Routing;

use Dystore\Api\Domain\Addresses\Contracts\AddressesController;
use Dystore\Api\Facades\Api;
use Dystore\Api\Routing\RouteGroup;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Routing\Relationships;
use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;

class AddressRouteGroup extends RouteGroup
{
    /**
     * Register routes.
     */
    public function routes(): void
    {
        JsonApiRoute::server('v1')
            ->prefix('v1')
            ->middleware('auth:'.Api::getAuthGuard())
            ->resources(function (ResourceRegistrar $server) {
                $server->resource($this->getPrefix(), AddressesController::class)
                    ->relationships(function (Relationships $relationships) {
                        $relationships->hasOne('country')->readOnly();
                        $relationships->hasOne('customer')->readOnly();
                    });
            });
    }
}
