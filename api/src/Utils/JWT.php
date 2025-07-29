<?php

declare(strict_types=1);

namespace IndoWater\Api\Utils;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use DateTime;

class JWT
{
    private static string $secret = '';
    private static int $ttl = 3600; // 1 hour
    private static int $refreshTtl = 604800; // 7 days
    private static string $algorithm = 'HS256';

    public static function setConfig(array $config): void
    {
        self::$secret = $config['secret'] ?? '';
        self::$ttl = $config['ttl'] ?? 3600;
        self::$refreshTtl = $config['refresh_ttl'] ?? 604800;
        self::$algorithm = $config['algorithm'] ?? 'HS256';
    }

    public static function encode(array $payload, ?int $ttl = null): string
    {
        $ttl = $ttl ?? self::$ttl;
        $now = time();
        
        $payload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $ttl,
            'nbf' => $now,
        ]);

        return FirebaseJWT::encode($payload, self::$secret, self::$algorithm);
    }

    public static function decode(string $token): array
    {
        try {
            $decoded = FirebaseJWT::decode($token, new Key(self::$secret, self::$algorithm));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            throw new \Exception('Token has expired', 401);
        } catch (SignatureInvalidException $e) {
            throw new \Exception('Token signature is invalid', 401);
        } catch (BeforeValidException $e) {
            throw new \Exception('Token is not yet valid', 401);
        } catch (\Exception $e) {
            throw new \Exception('Invalid token', 401);
        }
    }

    public static function generateAccessToken(array $user): string
    {
        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'type' => 'access',
        ];

        return self::encode($payload, self::$ttl);
    }

    public static function generateRefreshToken(array $user): string
    {
        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'type' => 'refresh',
        ];

        return self::encode($payload, self::$refreshTtl);
    }

    public static function validateToken(string $token): array
    {
        return self::decode($token);
    }

    public static function isExpired(string $token): bool
    {
        try {
            self::decode($token);
            return false;
        } catch (\Exception $e) {
            return true;
        }
    }

    public static function getTokenPayload(string $token): ?array
    {
        try {
            return self::decode($token);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function extractTokenFromHeader(string $authHeader): ?string
    {
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function getUserIdFromToken(string $token): ?string
    {
        $payload = self::getTokenPayload($token);
        return $payload['sub'] ?? null;
    }

    public static function getRoleFromToken(string $token): ?string
    {
        $payload = self::getTokenPayload($token);
        return $payload['role'] ?? null;
    }

    public static function isAccessToken(string $token): bool
    {
        $payload = self::getTokenPayload($token);
        return ($payload['type'] ?? '') === 'access';
    }

    public static function isRefreshToken(string $token): bool
    {
        $payload = self::getTokenPayload($token);
        return ($payload['type'] ?? '') === 'refresh';
    }
}