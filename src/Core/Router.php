<?php

namespace FoxTool\Yukon\Core;

use FoxTool\Yukon\Core\Container;
use FoxTool\Yukon\Core\Request;
use FoxTool\Yukon\Middleware\ApiAuthMiddleware;

class Router extends RouterController
{
    /**
     * @var array
     */
    protected array $config;

    /**
     * @var string
     */
    public $controller;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $guard;

    /**
     * @var string
     */
    public $authUser;

    /**
     * @var boolean
     */
    private $isRouteMatch = false;

    public function __construct()
    {
        try {
            $this->initRoutes();
            $this->parseURI();
            $uri = $this->getURI();
            $requestMethod = $this->getRequestMethod();

            foreach (self::$routes as $params) {
                list($method, $route, $callback, $middleware) = $params;

                $route = (strlen($route) > 1) ? rtrim($route, '/') : $route;
                $transformedRoute = preg_replace('/{[\w_-]+}/', '[\w_-]+', $route);

                if (preg_match('~^' . $transformedRoute . '$~i', $uri) ) {
                    if ($requestMethod == $method) {

                        if (gettype($callback) == 'object') {
                            $callback();
                        } else {
                            $attribute = $this->getAttributeParts($callback);
                            list($controller, $method) = $attribute;

                            $this->controller = $controller;
                            $this->method = $method;
                            $this->guard = $middleware;
                        }
                        $this->isRouteMatch = true;
                        break;
                    }
                }
            }

            if ($requestMethod === 'OPTIONS') {
                return false;
            }

            if (!$this->isRouteMatch) {
                throw new \Exception('404 - Page not found.');
            } else if ($this->isRouteMatch && !empty($this->controller)) {
                $params = $this->matchParams($uri, $route);
                $this->run($params);
            }

        } catch(\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    private function getControllerFullName()
    {
        try {
            $config = $_SERVER['DOCUMENT_ROOT'] . '/../configs/app.php';

            if (!file_exists($config)) {
                echo "There is no configuration file 'app.php' in the 'configs' catalog";
                return;
            }

            $this->config = require_once($config);
            $namespace = rtrim($this->config['controller_namespace'], '\\') . '\\';

            if (empty($namespace)) {
                echo "There is no 'controller_namespace' parameter in the 'configs/app.php' file";
                return;
            }

            return $namespace . $this->controller;
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * Compares URI string with template string from the route and builds array
     * with matched parameters.
     *
     * @param string $uri URI string
     * @param string $route Template string from the route
     * @return array
     */
    private function matchParams($uri, $route)
    {
        $params = array();
        $uriParts = explode('/', ltrim($uri, '/'));
        $routeParts = explode('/', ltrim($route, '/'));

        for ($i = 0; $i < count($routeParts); $i++) {
            if (isset($routeParts[$i]) && isset($uriParts[$i]) && $routeParts[$i] != $uriParts[$i]) {
                $paramName = preg_replace('/[{|}]/', '', strip_tags($routeParts[$i]));
                $params[] = $uriParts[$i];
            }
        }

        return $params;
    }

    private function getMethodMetaData($object, $method)
    {
        $data = new \stdClass();
        $data->paramsCount = 0;
        $data->hasRequest = false;

        $classMethod = new \ReflectionMethod(get_class($object), $method);
        $parameters = $classMethod->getParameters();

        foreach ($parameters as $value) {
            if ($value->name !== 'request') {
                $data->paramsCount++;
            } else {
                $data->hasRequest = true;
            }
        }

        return $data;
    }

    private function getAttributeParts($actionParams)
    {
        if ($actionParams == '') {
            throw new \Exception('Controller or action isn\'t defined for the route.');
        }

        if (stripos($actionParams, '@') == 0) {
            throw new \Exception('Action isn\'t defined for the route.');
        }

        return explode('@', $actionParams);
    }

    private function run(array $params)
    {
        try {
            if (is_null($this->controller) || $this->controller == '') {
                throw new \Exception('Controller not found.');

            }

            if (is_null($this->method) || $this->method == '') {
                throw new \Exception('Method not found.');
            }

            $controllerFullName = $this->getControllerFullName();
            $methodName = $this->method;

            if (!is_null($this->guard)) {
                if ($this->guard === 'api') {
                    $request = new Request();
                    $middleware = new ApiAuthMiddleware($request);
                    $this->authUser = $middleware->authenticate();
                }

                if ($this->guard === 'web') {
                    // TODO: Add the guard to protect routes with server sessions
                }
            }

            $container = new Container();
            $app = $container->resolve($controllerFullName);
            $app->setAuthUser($this->authUser);

            if (method_exists($app, $methodName)) {
                $metaData = $this->getMethodMetaData($app, $methodName);
                if ($metaData->paramsCount > count($params)) {
                    throw new \Exception("Parameter was defined in the controller method, but doesn't use in the route.");
                }

                if ($metaData->hasRequest) {
                    $request = new Request();
                    $app->$methodName($request, ...$params);
                } else {
                    $app->$methodName(...$params);
                }

            } else {
                throw new \Exception('Method: ' . $methodName . ' is not found in the class: ' . $controllerFullName);
            }
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}
