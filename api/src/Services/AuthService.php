<?php

declare(strict_types=1);

namespace IndoWater\Api\Services;

use IndoWater\Api\Models\User;
use IndoWater\Api\Repositories\UserRepository;
use IndoWater\Api\Utils\JWT;
use IndoWater\Api\Exceptions\AuthenticationException;
use IndoWater\Api\Exceptions\ValidationException;
use DateTime;

class AuthService
{
    private UserRepository $userRepository;
    private EmailService $emailService;

    public function __construct(UserRepository $userRepository, EmailService $emailService)
    {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !$user->verifyPassword($password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        if (!$user->isActive()) {
            throw new AuthenticationException('Account is not active');
        }

        // Update last login
        $this->userRepository->updateLastLogin($user->getId());

        // Generate tokens
        $userData = $user->toArray();
        $accessToken = JWT::generateAccessToken($userData);
        $refreshToken = JWT::generateRefreshToken($userData);

        // Store refresh token
        $this->userRepository->updateRememberToken($user->getId(), $refreshToken);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];
    }

    public function register(array $userData): User
    {
        // Validate email uniqueness
        if ($this->userRepository->emailExists($userData['email'])) {
            throw new ValidationException('Email already exists');
        }

        // Create user
        $user = new User();
        $user->setName($userData['name'])
             ->setEmail($userData['email'])
             ->setPassword($userData['password'])
             ->setPhone($userData['phone'] ?? null)
             ->setRole($userData['role'] ?? User::ROLE_CUSTOMER)
             ->setStatus(User::STATUS_PENDING);

        $user = $this->userRepository->save($user);

        // Send verification email
        $this->sendVerificationEmail($user);

        return $user;
    }

    public function refresh(string $refreshToken): array
    {
        try {
            $payload = JWT::validateToken($refreshToken);
            
            if (!JWT::isRefreshToken($refreshToken)) {
                throw new AuthenticationException('Invalid refresh token');
            }

            $user = $this->userRepository->findById($payload['sub']);
            
            if (!$user || !$user->isActive()) {
                throw new AuthenticationException('User not found or inactive');
            }

            // Generate new tokens
            $userData = $user->toArray();
            $accessToken = JWT::generateAccessToken($userData);
            $newRefreshToken = JWT::generateRefreshToken($userData);

            // Update refresh token
            $this->userRepository->updateRememberToken($user->getId(), $newRefreshToken);

            return [
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $newRefreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ];
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid refresh token');
        }
    }

    public function logout(string $userId): bool
    {
        return $this->userRepository->updateRememberToken($userId, null);
    }

    public function forgotPassword(string $email): bool
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            // Don't reveal if email exists
            return true;
        }

        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        
        // Store reset token (you might want to create a separate table for this)
        // For now, we'll use remember_token field temporarily
        $this->userRepository->updateRememberToken($user->getId(), $resetToken);

        // Send reset email
        $this->emailService->sendPasswordResetEmail($user, $resetToken);

        return true;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        // Find user by reset token
        $sql = "SELECT * FROM users WHERE remember_token = :token AND deleted_at IS NULL";
        $stmt = $this->userRepository->executeQuery($sql, [':token' => $token]);
        $userData = $stmt->fetch();

        if (!$userData) {
            throw new ValidationException('Invalid reset token');
        }

        $user = $this->userRepository->findById($userData['id']);
        
        if (!$user) {
            throw new ValidationException('User not found');
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->userRepository->updatePassword($user->getId(), $hashedPassword);
        
        // Clear reset token
        $this->userRepository->updateRememberToken($user->getId(), null);

        return true;
    }

    public function verifyEmail(string $token): bool
    {
        // This would typically use a separate email verification tokens table
        // For simplicity, we'll assume the token is the user ID for now
        $user = $this->userRepository->findById($token);
        
        if (!$user) {
            throw new ValidationException('Invalid verification token');
        }

        if ($user->isEmailVerified()) {
            return true;
        }

        $this->userRepository->markEmailAsVerified($user->getId());
        
        // Activate user if they were pending email verification
        if ($user->getStatus() === User::STATUS_PENDING) {
            $user->setStatus(User::STATUS_ACTIVE);
            $this->userRepository->save($user);
        }

        return true;
    }

    public function resendVerification(string $email): bool
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            throw new ValidationException('User not found');
        }

        if ($user->isEmailVerified()) {
            throw new ValidationException('Email already verified');
        }

        $this->sendVerificationEmail($user);

        return true;
    }

    public function getCurrentUser(string $token): ?User
    {
        try {
            $payload = JWT::validateToken($token);
            
            if (!JWT::isAccessToken($token)) {
                return null;
            }

            return $this->userRepository->findById($payload['sub']);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function validateToken(string $token): bool
    {
        try {
            JWT::validateToken($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function sendVerificationEmail(User $user): void
    {
        $verificationToken = $user->getId(); // Simplified for now
        $this->emailService->sendEmailVerification($user, $verificationToken);
    }

    public function hasPermission(User $user, string $permission): bool
    {
        // Define role-based permissions
        $permissions = [
            User::ROLE_SUPERADMIN => ['*'], // All permissions
            User::ROLE_CLIENT => [
                'clients.view', 'clients.update',
                'customers.create', 'customers.view', 'customers.update', 'customers.delete',
                'properties.create', 'properties.view', 'properties.update', 'properties.delete',
                'meters.create', 'meters.view', 'meters.update', 'meters.delete',
                'payments.view', 'credits.view', 'reports.view',
            ],
            User::ROLE_CUSTOMER => [
                'profile.view', 'profile.update',
                'meters.view', 'credits.view', 'payments.create', 'payments.view',
            ],
        ];

        $userPermissions = $permissions[$user->getRole()] ?? [];
        
        return in_array('*', $userPermissions) || in_array($permission, $userPermissions);
    }
}