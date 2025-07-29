<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use IndoWater\Api\Controllers\HealthController;

return function (App $app) {
    // Health Check
    $app->get('/health', [HealthController::class, 'check']);
    
    // Simple test route
    $app->get('/test', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'API is working!',
            'timestamp' => date('Y-m-d H:i:s')
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};