<?php

namespace Yukon\Core;

use Yukon\Core\App\Router;

class Starter
{
    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    }

    public function run()
    {
        $router = new Router();
    }
}
