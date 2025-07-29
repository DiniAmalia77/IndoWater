<?php

declare(strict_types=1);

namespace IndoWater\Api\Utils;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;
    private static array $config = [];

    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }

        return self::$connection;
    }

    private static function connect(): void
    {
        $host = self::$config['host'] ?? 'localhost';
        $port = self::$config['port'] ?? 3306;
        $database = self::$config['database'] ?? '';
        $username = self::$config['username'] ?? '';
        $password = self::$config['password'] ?? '';
        $charset = self::$config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}",
        ];

        try {
            self::$connection = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollback(): bool
    {
        return self::getConnection()->rollback();
    }

    public static function inTransaction(): bool
    {
        return self::getConnection()->inTransaction();
    }

    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    public static function quote(string $string): string
    {
        return self::getConnection()->quote($string);
    }

    public static function prepare(string $statement): \PDOStatement
    {
        return self::getConnection()->prepare($statement);
    }

    public static function query(string $statement): \PDOStatement
    {
        return self::getConnection()->query($statement);
    }

    public static function exec(string $statement): int
    {
        return self::getConnection()->exec($statement);
    }
}