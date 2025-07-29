<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\HealthController;
use App\Middleware\JwtMiddleware;

return function (App $app) {
    // Health Check
    $app->get('/health', [HealthController::class, 'check']);

    // API Routes
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Auth Routes
        $group->group('/auth', function (RouteCollectorProxy $group) {
            $group->post('/login', [AuthController::class, 'login']);
            $group->post('/register', [AuthController::class, 'register']);
            $group->post('/forgot-password', [AuthController::class, 'forgotPassword']);
            $group->post('/reset-password', [AuthController::class, 'resetPassword']);
            $group->get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
            $group->post('/resend-verification', [AuthController::class, 'resendVerification']);
            
            // Protected auth routes
            $group->group('', function (RouteCollectorProxy $group) {
                $group->post('/logout', [AuthController::class, 'logout']);
            })->add(new JwtMiddleware($app->getContainer()));
        });

        // User Routes
        $group->group('/users', function (RouteCollectorProxy $group) {
            $group->get('/me', [UserController::class, 'getMe']);
            $group->put('/me', [UserController::class, 'updateProfile']);
            $group->put('/me/password', [UserController::class, 'updatePassword']);
        })->add(new JwtMiddleware($app->getContainer()));
    });

    // Fallback for undefined routes
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => 'Route not found',
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    });
};