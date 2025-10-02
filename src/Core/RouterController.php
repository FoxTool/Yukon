<?php

namespace FoxTool\Yukon\Core;

use FoxTool\Yukon\Core\Request;

class RouterController
{
    public static $routes = [];
    public static $prefix;
    protected $_prefix = '';
    public $uri;
    public $queryString;
    public $queryParams = [];
    public $authUser = [];
    private static $self;

    private static function init($route, $callback)
    {
        $self = __CLASS__;
        self::$self = new $self();

        if ($route == '') {
            throw new \Exception('Set the <b>route</b> parameter for the <b>' . $callback . '</b>');

        }

        if (!empty(self::$prefix)) {
          $route = self::$prefix . $route;
        }

        return $route;
    }

    /**
     * Define route for 'GET' method.
     *
     * @param string $route Route pattern
     * @param string $callback Define controller and method
     */
    public static function get($route, $callback)
    {
        $route = self::init($route, $callback);
        self::$routes[] = array('GET', $route, $callback, null);

        return self::$self;
    }

    /**
     * Define route for 'POST' method.
     *
     * @param string $route Route pattern
     * @param string $callback Define controller and method
     */
    public static function post($route, $callback)
    {
        $route = self::init($route, $callback);
        self::$routes[] = array('POST', $route, $callback, null);

        return self::$self;
    }

    /**
     * Define route for 'PUT' method.
     *
     * @param string $route Route pattern
     * @param string $callback Define controller and method
     */
    public static function put($route, $callback)
    {
        $route = self::init($route, $callback);
        self::$routes[] = array('PUT', $route, $callback, null);

        return self::$self;
    }

    /**
     * Define route for 'PATCH' method.
     *
     * @param string $route Route pattern
     * @param string $callback Define controller and method
     */
    public static function patch($route, $callback)
    {
        $route = self::init($route, $callback);
        self::$routes[] = array('PATCH', $route, $callback, null);

        return self::$self;
    }

    /**
     * Define route for 'DELETE' method.
     *
     * @param string $route Route pattern
     * @param string $callback Define controller and method
     */
    public static function delete($route, $callback)
    {
        $route = self::init($route, $callback);
        self::$routes[] = array('DELETE', $route, $callback, null);

        return self::$self;
    }

    /**
     * Initialize the routes file.
     */
    protected function initRoutes()
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/../configs/routes.php')) {
            include($_SERVER['DOCUMENT_ROOT'] . '/../configs/routes.php');
        } else {
            throw new \Exception('Routes file does not exists!');
        }
    }

    /**
     * Return object with prefix property.
     *
     * @param string $prefix Prefix for the route
     * @return object Yukon\Core\RouterController
     */
    public static function prefix($prefix)
    {
        if (isset($prefix) && !empty($prefix)) {
            self::$self->_prefix = $prefix;
        }

        return self::$self;
    }

    /**
     * Group routes by common prefix.
     *
     * @param Closure Callback function
     * @return void
     */
    public function group($callback)
    {
        self::$prefix = $this->_prefix;
        $callback();
        self::$prefix = null;
    }

    protected function parseURI()
    {
        $fullUri = $_SERVER['REQUEST_URI'];
        $pos = stripos($fullUri, '?');

        if ($pos > 0) {
            $this->uri = substr($fullUri, 0, $pos);
        } else {
            $this->uri = $fullUri;
        }
    }

    /**
     * Returns URI from URL string.
     */
    protected function getURI()
    {
        return $this->uri;
    }

    /**
     * Returns request method.
     */
    protected function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function middleware($middleware)
    {
        /**
         * Index number 3 is set as null by default and is used for store
         * of the middleware name. It's not the best solution but allows avoiding
         * complex structure with associative array and allows using "list" function
         * for each route item in the routes array.
         */
        self::$routes[count(self::$routes) -1][3] = $middleware;
    }
}
