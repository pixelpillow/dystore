<?php

namespace Dystcz\LunarProductNotification\Domain\JsonApi\V1;

use Dystcz\LunarApi\Domain\JsonApi\Servers\Server as BaseServer;

class Server extends BaseServer
{
    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    protected function allSchemas(): array
    {
        return [

        ];
    }
}
