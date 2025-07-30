<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\ClientController;
use App\Controllers\PropertyController;
use App\Controllers\CustomerController;
use App\Controllers\MeterController;
use App\Controllers\PaymentController;
use App\Controllers\HealthController;
use App\Middleware\JwtMiddleware;

return function (App $app) {
    // Health Check
    $app->get('/health', [HealthController::class, 'check']);

    // API Routes
    $app->group('/api', function (RouteCollectorProxy $group) use ($app) {
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
                $group->get('/user', [AuthController::class, 'getUser']);
            })->add(new JwtMiddleware($app->getContainer()));
        });

        // User Routes
        $group->group('/users', function (RouteCollectorProxy $group) {
            $group->get('/me', [UserController::class, 'getMe']);
            $group->put('/me', [UserController::class, 'updateProfile']);
            $group->put('/me/password', [UserController::class, 'updatePassword']);
        })->add(new JwtMiddleware($app->getContainer()));
        
        // Client Routes (for superadmin)
        $group->group('/clients', function (RouteCollectorProxy $group) {
            $group->get('', [ClientController::class, 'getClients']);
            $group->get('/{id}', [ClientController::class, 'getClient']);
            $group->post('', [ClientController::class, 'createClient']);
            $group->put('/{id}', [ClientController::class, 'updateClient']);
            $group->delete('/{id}', [ClientController::class, 'deleteClient']);
            $group->get('/{id}/stats', [ClientController::class, 'getClientStats']);
        })->add(new JwtMiddleware($app->getContainer()));
        
        // Property Routes (for client and superadmin)
        $group->group('/properties', function (RouteCollectorProxy $group) {
            $group->get('', [PropertyController::class, 'getProperties']);
            $group->get('/{id}', [PropertyController::class, 'getProperty']);
            $group->post('', [PropertyController::class, 'createProperty']);
            $group->put('/{id}', [PropertyController::class, 'updateProperty']);
            $group->delete('/{id}', [PropertyController::class, 'deleteProperty']);
            $group->get('/{id}/stats', [PropertyController::class, 'getPropertyStats']);
        })->add(new JwtMiddleware($app->getContainer()));
        
        // Customer Routes (for client, superadmin, and customer)
        $group->group('/customers', function (RouteCollectorProxy $group) {
            $group->get('', [CustomerController::class, 'getCustomers']);
            $group->get('/{id}', [CustomerController::class, 'getCustomer']);
            $group->post('', [CustomerController::class, 'createCustomer']);
            $group->put('/{id}', [CustomerController::class, 'updateCustomer']);
            $group->delete('/{id}', [CustomerController::class, 'deleteCustomer']);
            $group->get('/{id}/consumption', [CustomerController::class, 'getCustomerConsumption']);
            $group->get('/{id}/payments', [CustomerController::class, 'getCustomerPayments']);
        })->add(new JwtMiddleware($app->getContainer()));
        
        // Meter Routes (for client, superadmin, and customer)
        $group->group('/meters', function (RouteCollectorProxy $group) {
            $group->get('', [MeterController::class, 'getMeters']);
            $group->get('/{id}', [MeterController::class, 'getMeter']);
            $group->post('', [MeterController::class, 'createMeter']);
            $group->put('/{id}', [MeterController::class, 'updateMeter']);
            $group->delete('/{id}', [MeterController::class, 'deleteMeter']);
            $group->post('/{id}/readings', [MeterController::class, 'submitReading']);
            $group->put('/{id}/readings/{reading_id}', [MeterController::class, 'verifyReading']);
            $group->get('/{id}/readings', [MeterController::class, 'getMeterReadings']);
        })->add(new JwtMiddleware($app->getContainer()));
        
        // Payment Routes (for client, superadmin, and customer)
        $group->group('/payments', function (RouteCollectorProxy $group) {
            $group->get('', [PaymentController::class, 'getPayments']);
            $group->get('/{id}', [PaymentController::class, 'getPayment']);
            $group->post('', [PaymentController::class, 'createPayment']);
            $group->put('/{id}', [PaymentController::class, 'updatePayment']);
            $group->delete('/{id}', [PaymentController::class, 'deletePayment']);
            $group->post('/{id}/process', [PaymentController::class, 'processPayment']);
            $group->get('/{id}/invoice', [PaymentController::class, 'generateInvoice']);
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