<?php

namespace Yukon\Core;

class RouterController
{
    public static $routes = [];
    public static $prefix;
    protected $_prefix = '';
    public $uri;
    public $queryString;
    public $queryParams = [];

    /**
     * Define route for 'GET' method.
     *
     * @param string $route Route pattern
     * @param string $callback Define controller and method
     */
    public static function get($route, $callback)
    {
        if ($route == '') {
            return false;
        }

        if (!empty(self::$prefix)) {
          $route = self::$prefix . $route;
        }

        self::$routes[] = array('GET', $route, $callback);
    }

    /**
     * Define route for 'POST' method.
     *
     * @param string $route Route pattern
     * @param string $callback Define controller and method
     */
    public static function post($route, $callback)
    {
        if ($route == '') {
            return false;
        }

        if (!empty(self::$prefix)) {
          $route = self::$prefix . $route;
        }

        self::$routes[] = array('POST', $route, $callback);
    }

    /**
     * Define route for 'PUT' method.
     *
     * @param string $route Route pattern
     * @param string $callback Define controller and method
     */
    public static function put($route, $callback)
    {
        if ($route == '') {
            return false;
        }

        if (!empty(self::$prefix)) {
          $route = self::$prefix . $route;
        }

        self::$routes[] = array('PUT', $route, $callback);
    }

    /**
     * Define route for 'PATCH' method.
     *
     * @param string $route Route pattern
     * @param string $callback Define controller and method
     */
    public static function patch($route, $callback)
    {
        if ($route == '') {
            return false;
        }

        if (!empty(self::$prefix)) {
          $route = self::$prefix . $route;
        }

        self::$routes[] = array('PATCH', $route, $callback);
    }

    /**
     * Define route for 'DELETE' method.
     *
     * @param string $route Route pattern
     * @param string $callback Define controller and method
     */
    public static function delete($route, $callback)
    {
        if ($route == '') {
            return false;
        }

        if (!empty(self::$prefix)) {
          $route = self::$prefix . $route;
        }

        self::$routes[] = array('DELETE', $route, $callback);
    }

    /**
     * Initialize the routes file.
     */
    protected function initRoutes()
    {
        if (file_exists(ROOT . '/../config/routes.php')) {
            include(ROOT . '/../config/routes.php');
        } else {
            throw new \Exception('Routes file does not exists!');
        }
    }

    /**
     * Return object with prefix property.
     *
     * @param string $prefix Prefix for the route
     * @return object Yukon\Core\App\RouterController
     */
    public static function prefix($prefix)
    {
      $self = __CLASS__;
      $self = new $self();
      if (isset($prefix) && !empty($prefix)) {
        $self->_prefix = $prefix;
      }

      return $self;
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
}
