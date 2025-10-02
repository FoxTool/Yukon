<?php

namespace FoxTool\Yukon\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use FoxTool\Yukon\Contracts\AuthMiddlewareInterface;

use FoxTool\Yukon\Core\Request;
use FoxTool\Yukon\Core\Response;

class ApiAuthMiddleware implements AuthMiddlewareInterface
{
    private $user;
    private Request $request;
    private array $settings = [];
    private string $secretKey;

    public function __construct(Request $request)
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/../configs/settings.php')) {
            $this->settings = require_once($_SERVER['DOCUMENT_ROOT'] . '/../configs/settings.php');
        } else {
            throw new \Exception('There is no "/configs/settings.php" file');
        }

        $this->request = $request;
        $this->secretKey = $this->settings['JWT_SECRET'];
    }

    public function authenticate(): \stdClass|Response
    {
        $authHeader = $this->request->headers('Authorization');

        if (empty($authHeader)) {
            return (new Response())->header('Content-Type', 'application/json')->json([
                "error" => 'Unauthorized',
                "message" => "You are unauthorized"
            ]);
        }

        $token = substr($authHeader, 7);

        $this->user = $this->validateApiToken($token);

        if (is_null($this->user)) {
            return (new Response(401))->header('Content-Type', 'application/json')->json([
                "error" => 'Unauthorized',
                "message" => "Token validation error"
            ]);
        }

        if ($this->isTokenExpired($this->user->exp)) {
            return (new Response(401))->header('Content-Type', 'application/json')->json([
                "error" => 'Unauthorized',
                "message" => "Token is expired"
            ]);
        }

        return $this->user;
    }

    private function validateApiToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

            if (isset($decoded->type) && $decoded->type === 'api_token') {
                return $decoded;
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function isTokenExpired($expiredAt): bool
    {
        $tokenTimestamp = new \DateTime();
        $tokenTimestamp->setTimestamp($expiredAt);

        $now = new \DateTime();

        if ($tokenTimestamp->getTimestamp() > $now->getTimestamp()) {
            return false;
        }

        return true;
    }

}
