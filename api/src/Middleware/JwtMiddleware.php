<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtMiddleware implements MiddlewareInterface
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (!$authHeader) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Authorization header is required',
            ], 401);
        }
        
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid authorization header format',
            ], 401);
        }
        
        $token = $matches[1];
        
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            
            // Add the user to the request attributes
            $request = $request->withAttribute('user', (array) $decoded->user);
            
            return $handler->handle($request);
        } catch (ExpiredException $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Token has expired',
            ], 401);
        } catch (SignatureInvalidException $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid token signature',
            ], 401);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid token',
            ], 401);
        }
    }

    private function jsonResponse(array $data, int $status = 200): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($data));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}