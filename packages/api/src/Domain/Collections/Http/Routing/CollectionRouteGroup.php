<?php

namespace Dystore\Api\Domain\Collections\Http\Routing;

use Dystore\Api\Domain\Collections\Contracts\CollectionsController;
use Dystore\Api\Routing\Contracts\RouteGroup as RouteGroupContract;
use Dystore\Api\Routing\RouteGroup;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Routing\Relationships;
use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;

class CollectionRouteGroup extends RouteGroup implements RouteGroupContract
{
    /**
     * Register routes.
     */
    public function routes(): void
    {
        JsonApiRoute::server('v1')
            ->prefix('v1')
            ->resources(function (ResourceRegistrar $server) {
                $server->resource($this->getPrefix(), CollectionsController::class)
                    ->relationships(function (Relationships $relationships) {
                        $relationships->hasMany('images')->readOnly();
                        $relationships->hasMany('products')->readOnly();
                        $relationships->hasOne('default_url')->readOnly();
                    })
                    ->only('index', 'show')
                    ->readOnly();
            });
    }
}
