<?php

namespace Dystore\Api\Domain\JsonApi\V1;

use Dystore\Api\Domain\JsonApi\Servers\Server as BaseServer;
use Illuminate\Support\Facades\Config;

class Server extends BaseServer
{
    /**
     * Set base server URI.
     */
    protected function setBaseUri(string $path = 'v1'): void
    {
        $prefix = Config::get('dystore.general.route_prefix');

        $this->baseUri = "/{$prefix}/{$path}";
    }

    /**
     * Bootstrap the server when it is handling an HTTP request.
     */
    public function serving(): void
    {
        //
    }

    /**
     * Get the server's list of schemas.
     */
    protected function allSchemas(): array
    {
        return parent::allSchemas();
    }
}
