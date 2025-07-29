<?php

declare(strict_types=1);

namespace IndoWater\Api\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use IndoWater\Api\Services\AuthService;
use IndoWater\Api\Utils\JWT;

class AuthMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            return $this->unauthorizedResponse();
        }

        $token = JWT::extractTokenFromHeader($authHeader);
        
        if (!$token) {
            return $this->unauthorizedResponse();
        }

        try {
            $user = $this->authService->getCurrentUser($token);
            
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            // Add user to request attributes
            $request = $request->withAttribute('user', $user);
            $request = $request->withAttribute('token', $token);

            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse();
        }
    }

    private function unauthorizedResponse(): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => 'Unauthorized',
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}