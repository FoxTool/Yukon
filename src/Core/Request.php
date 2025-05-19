<?php

namespace FoxTool\Yukon\Core;

use FoxTool\Yukon\Contracts\RequestInterface;

class Request implements RequestInterface
{
    private array $headers = [];
    private array $data = [];
    
    public function __construct(array $input = [])
    {
        $this->data = $input;
        $this->getHeaders();
        $this->getParameters();
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function all(): array
    {
        return $this->data;
    }

    /**
     * Method gets headers from request and puts they into the private
     * 'headers' property.
     *
     * Default Params:
     *      'Accept',
     *      'Cache-Control',
     *      'Origin',
     *      'User-Agent',
     *      'Host',
     *      'Connection',
     *      'Content-Length',
     *      'Content-Type',
     *      'Accept-Encoding',
     *      'Accept-Language'
     */
    private function getHeaders()
    {

        if(!function_exists('getallheaders')) {
            foreach($_SERVER as $name => $value) {
                if(substr($name, 0, 5) == 'HTTP_') {
                    $this->headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        } else {
          foreach (getallheaders() as $name => $value) {
              $this->headers[$name] = $value;
          }
        }

      return $this->headers;
    }

    private function getParameters()
    {
        // Get parameters from global $_GET array.
        if (isset($_GET) && !empty($_GET)) {
            $parameters = $_GET;
        }

        // Get parameters from global $_POST array.
        if (isset($_POST) && !empty($_POST)) {
            $parameters = $_POST;
        }

        if (isset($_FILES) && !empty($_FILES)) {
            if (empty($parameters)) {
                $parameters = $_FILES;
            } else {
                $parameters['files'] = $_FILES;
            }
        }

        // Get parameters from the php://input when Content-Type is 'json' string.
        if ($this->headers('Content-Type') == 'application/json') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!empty($data)) {
                $parameters = $data;
            }
        }

        if (isset($parameters) && !empty($parameters) && is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                if (is_array($value)) {
                    $this->$key = $value;
                } else {
                    $this->$key = trim(htmlspecialchars(stripslashes($value)));
                }
            }
        }
    }

    public function headers($name)
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        return false;
    }

    public function get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return false;
    }

}
