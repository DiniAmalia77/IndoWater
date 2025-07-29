<?php

declare(strict_types=1);

namespace IndoWater\Api\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use IndoWater\Api\Models\User;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $user = $request->getAttribute('user');
        
        if (!$user instanceof User) {
            return $this->forbiddenResponse();
        }

        if (!in_array($user->getRole(), $this->allowedRoles)) {
            return $this->forbiddenResponse();
        }

        return $handler->handle($request);
    }

    private function forbiddenResponse(): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => 'Forbidden: Insufficient permissions',
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(403);
    }

    public static function superAdmin(): self
    {
        return new self([User::ROLE_SUPERADMIN]);
    }

    public static function client(): self
    {
        return new self([User::ROLE_SUPERADMIN, User::ROLE_CLIENT]);
    }

    public static function customer(): self
    {
        return new self([User::ROLE_SUPERADMIN, User::ROLE_CLIENT, User::ROLE_CUSTOMER]);
    }

    public static function adminOnly(): self
    {
        return new self([User::ROLE_SUPERADMIN, User::ROLE_CLIENT]);
    }
}