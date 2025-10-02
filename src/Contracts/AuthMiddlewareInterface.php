<?php

namespace FoxTool\Yukon\Contracts;

use FoxTool\Yukon\Core\Response;

interface AuthMiddlewareInterface
{
    public function authenticate(): \stdClass|Response;
}
