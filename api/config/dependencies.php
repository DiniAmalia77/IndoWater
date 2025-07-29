<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

// Import our classes
use IndoWater\Api\Utils\Database;
use IndoWater\Api\Utils\JWT as JWTUtil;
use IndoWater\Api\Services\AuthService;
use IndoWater\Api\Services\UserService;
use IndoWater\Api\Services\EmailService;
use IndoWater\Api\Repositories\UserRepository;
use IndoWater\Api\Repositories\ClientRepository;
use IndoWater\Api\Controllers\AuthController;
use IndoWater\Api\Controllers\UserController;
use IndoWater\Api\Controllers\HealthController;
use IndoWater\Api\Middleware\AuthMiddleware;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Logger
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerSettings = $settings['log'];

            $logger = new Logger($settings['app']['name']);
            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler(
                __DIR__ . '/../logs/' . $loggerSettings['channel'] . '.log',
                Logger::getLevelName(strtoupper($loggerSettings['level']))
            );
            $logger->pushHandler($handler);

            return $logger;
        },

        // Database Connection
        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $dbSettings = $settings['db'];

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $dbSettings['driver'],
                $dbSettings['host'],
                $dbSettings['port'],
                $dbSettings['database'],
                $dbSettings['charset']
            );

            $pdo = new PDO(
                $dsn,
                $dbSettings['username'],
                $dbSettings['password']
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            return $pdo;
        },

        // Doctrine Entity Manager
        EntityManager::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $dbSettings = $settings['db'];

            $config = ORMSetup::createAttributeMetadataConfiguration(
                [__DIR__ . '/../src/Entities'],
                $settings['app']['debug']
            );

            $connectionParams = [
                'driver' => $dbSettings['driver'],
                'host' => $dbSettings['host'],
                'port' => $dbSettings['port'],
                'dbname' => $dbSettings['database'],
                'user' => $dbSettings['username'],
                'password' => $dbSettings['password'],
                'charset' => $dbSettings['charset'],
            ];

            $connection = DriverManager::getConnection($connectionParams, $config);
            return new EntityManager($connection, $config);
        },



        // Mailer
        Mailer::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $mailSettings = $settings['mail'];
            
            $dsn = sprintf(
                '%s://%s:%s@%s:%s',
                $mailSettings['driver'],
                $mailSettings['username'],
                $mailSettings['password'],
                $mailSettings['host'],
                $mailSettings['port']
            );
            
            $transport = Transport::fromDsn($dsn);
            return new Mailer($transport);
        },

        // HTTP Client
        Client::class => function (ContainerInterface $c) {
            return new Client([
                'timeout' => 5.0,
                'verify' => false,
            ]);
        },

        // Payment Gateway Factory
        \IndoWater\Api\Services\PaymentGateway\PaymentGatewayFactory::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            return new \IndoWater\Api\Services\PaymentGateway\PaymentGatewayFactory(
                $c->get(LoggerInterface::class),
                $settings['payment_gateways'] ?? []
            );
        },

        // Payment Service
        \IndoWater\Api\Services\PaymentService::class => function (ContainerInterface $c) {
            return new \IndoWater\Api\Services\PaymentService(
                $c->get(\IndoWater\Api\Repositories\PaymentRepository::class),
                $c->get(\IndoWater\Api\Repositories\CreditRepository::class),
                $c->get(\IndoWater\Api\Repositories\CustomerRepository::class),
                $c->get(\IndoWater\Api\Services\PaymentGateway\PaymentGatewayFactory::class),
                $c->get(LoggerInterface::class)
            );
        },

        // JWT
        JWT::class => function (ContainerInterface $c) {
            return new JWT();
        },

        // Database Configuration
        'database' => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $dbSettings = $settings['db'];
            
            Database::setConfig([
                'host' => $dbSettings['host'],
                'port' => $dbSettings['port'],
                'database' => $dbSettings['database'],
                'username' => $dbSettings['username'],
                'password' => $dbSettings['password'],
                'charset' => $dbSettings['charset'] ?? 'utf8mb4',
            ]);
            
            return Database::getConnection();
        },

        // JWT Utility
        JWTUtil::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $jwtSettings = $settings['jwt'];
            
            JWTUtil::setConfig([
                'secret' => $jwtSettings['secret'],
                'ttl' => $jwtSettings['ttl'],
                'refresh_ttl' => $jwtSettings['refresh_ttl'],
            ]);
            
            return new JWTUtil();
        },

        // Email Service
        EmailService::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            return new EmailService([
                'mail_host' => $settings['mail']['host'],
                'mail_port' => $settings['mail']['port'],
                'mail_username' => $settings['mail']['username'],
                'mail_password' => $settings['mail']['password'],
                'mail_encryption' => $settings['mail']['encryption'],
                'mail_from_address' => $settings['mail']['from_address'],
                'mail_from_name' => $settings['mail']['from_name'],
                'app_url' => $settings['app']['url'],
                'frontend_url' => $settings['app']['frontend_url'] ?? 'http://localhost:3000',
            ]);
        },

        // Repositories
        UserRepository::class => function (ContainerInterface $c) {
            $c->get('database'); // Initialize database
            return new UserRepository();
        },

        ClientRepository::class => function (ContainerInterface $c) {
            $c->get('database'); // Initialize database
            return new ClientRepository();
        },

        // Services
        AuthService::class => function (ContainerInterface $c) {
            return new AuthService(
                $c->get(UserRepository::class),
                $c->get(EmailService::class)
            );
        },

        UserService::class => function (ContainerInterface $c) {
            return new UserService(
                $c->get(UserRepository::class),
                $c->get(EmailService::class)
            );
        },

        // Controllers
        HealthController::class => function (ContainerInterface $c) {
            return new HealthController();
        },

        AuthController::class => function (ContainerInterface $c) {
            return new AuthController($c->get(AuthService::class));
        },

        UserController::class => function (ContainerInterface $c) {
            return new UserController($c->get(UserService::class));
        },

        // Middleware
        AuthMiddleware::class => function (ContainerInterface $c) {
            return new AuthMiddleware($c->get(AuthService::class));
        },
    ]);
};