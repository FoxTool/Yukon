<?php
namespace Yukon\Core;

use Yukon\Core\App\Request;

class Router extends RouterController
{

    /**
     * @var string
     */
    public $controller;

    /**
     * @var string
     */
    public $method;

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
                list($method, $route, $callback) = $params;

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

            $controllerFullName = '\Yukon\Controller\\' . $this->controller;
            $methodName = $this->method;

            $app = new $controllerFullName();
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
