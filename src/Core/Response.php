<?php
namespace Yukon\Core;

use Yukon\Contracts\HTTPResponse;

/**
 * Class implements finctionality for HTTP response.
 */
class Response implements HTTPResponse
{

    public static $codes = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        400 => 'Bad Request',
        401 => 'Unathorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway'
    ];

    public function __construct($status = 200) {
        if (array_key_exists($status, self::$codes)) {
            header("HTTP/1.1 {$status} " . self::$codes[$status]);
        }
    }

    public function header($key, $value = null)
    {
        header("{$key}: {$value}");
        return $this;
    }

    public function json($data)
    {
        echo json_encode($data);
    }

    public function content($data)
    {
        echo htmlspecialchars($data);
    }
}
