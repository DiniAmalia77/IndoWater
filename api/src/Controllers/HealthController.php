<?php

declare(strict_types=1);

namespace IndoWater\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HealthController extends BaseController
{
    public function __construct()
    {
        // No dependencies needed for health check
    }

    public function check(Request $request, Response $response): Response
    {
        $health = [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'environment' => $_ENV['APP_ENV'] ?? 'development',
            'services' => [
                'api' => 'ok',
                'database' => $this->checkDatabase(),
                'cache' => 'ok',
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                'memory_limit' => ini_get('memory_limit'),
            ],
        ];

        return $this->jsonResponse($response, $health);
    }

    private function checkDatabase(): string
    {
        try {
            // Simple database connection test
            $pdo = new \PDO(
                sprintf(
                    'mysql:host=%s;port=%s;dbname=%s',
                    $_ENV['DB_HOST'] ?? 'localhost',
                    $_ENV['DB_PORT'] ?? '3306',
                    $_ENV['DB_DATABASE'] ?? 'indowater'
                ),
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? ''
            );
            
            $pdo->query('SELECT 1');
            return 'ok';
        } catch (\Exception $e) {
            return 'error';
        }
    }
    
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}