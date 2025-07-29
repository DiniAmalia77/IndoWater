<?php

declare(strict_types=1);

namespace IndoWater\Api\Services;

use IndoWater\Api\Models\User;
use IndoWater\Api\Repositories\UserRepository;
use IndoWater\Api\Exceptions\NotFoundException;
use IndoWater\Api\Exceptions\ValidationException;

class UserService
{
    private UserRepository $userRepository;
    private EmailService $emailService;

    public function __construct(UserRepository $userRepository, EmailService $emailService)
    {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }

    public function getUsers(array $pagination, array $search, array $filters): array
    {
        if (!empty($search['search'])) {
            $users = $this->userRepository->searchUsers(
                $search['search'],
                $pagination['limit'],
                $pagination['offset']
            );
            $total = count($users); // Simplified for search
        } else {
            $users = $this->userRepository->findAll($filters, $pagination['limit'], $pagination['offset']);
            $total = $this->userRepository->count($filters);
        }

        return [
            'users' => $users,
            'total' => $total,
        ];
    }

    public function getUserById(string $id): User
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            throw new NotFoundException('User not found');
        }

        return $user;
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function createUser(array $data): User
    {
        // Validate email uniqueness
        if ($this->userRepository->emailExists($data['email'])) {
            throw new ValidationException('Email already exists');
        }

        // Validate role
        $allowedRoles = [User::ROLE_SUPERADMIN, User::ROLE_CLIENT, User::ROLE_CUSTOMER];
        if (!in_array($data['role'], $allowedRoles)) {
            throw new ValidationException('Invalid role');
        }

        // Create user
        $user = new User();
        $user->setName($data['name'])
             ->setEmail($data['email'])
             ->setPassword($data['password'])
             ->setPhone($data['phone'] ?? null)
             ->setRole($data['role'])
             ->setStatus($data['status'] ?? User::STATUS_PENDING);

        $user = $this->userRepository->save($user);

        // Send welcome email
        $this->emailService->sendWelcomeEmail($user);

        return $user;
    }

    public function updateUser(string $id, array $data): User
    {
        $user = $this->getUserById($id);

        // Validate email uniqueness if email is being updated
        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            if ($this->userRepository->emailExists($data['email'], $id)) {
                throw new ValidationException('Email already exists');
            }
            $user->setEmail($data['email']);
        }

        // Update other fields
        if (isset($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['phone'])) {
            $user->setPhone($data['phone']);
        }

        if (isset($data['role'])) {
            $allowedRoles = [User::ROLE_SUPERADMIN, User::ROLE_CLIENT, User::ROLE_CUSTOMER];
            if (!in_array($data['role'], $allowedRoles)) {
                throw new ValidationException('Invalid role');
            }
            $user->setRole($data['role']);
        }

        if (isset($data['status'])) {
            $allowedStatuses = [User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_PENDING, User::STATUS_SUSPENDED];
            if (!in_array($data['status'], $allowedStatuses)) {
                throw new ValidationException('Invalid status');
            }
            $user->setStatus($data['status']);
        }

        return $this->userRepository->save($user);
    }

    public function deleteUser(string $id): bool
    {
        $user = $this->getUserById($id);
        
        // Prevent deletion of superadmin users
        if ($user->isSuperAdmin()) {
            throw new ValidationException('Cannot delete superadmin user');
        }

        return $this->userRepository->delete($id);
    }

    public function updatePassword(string $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->getUserById($userId);

        if (!$user->verifyPassword($currentPassword)) {
            throw new ValidationException('Current password is incorrect');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->userRepository->updatePassword($userId, $hashedPassword);
    }

    public function activateUser(string $id): User
    {
        $user = $this->getUserById($id);
        $user->setStatus(User::STATUS_ACTIVE);
        
        return $this->userRepository->save($user);
    }

    public function deactivateUser(string $id): User
    {
        $user = $this->getUserById($id);
        $user->setStatus(User::STATUS_INACTIVE);
        
        return $this->userRepository->save($user);
    }

    public function suspendUser(string $id): User
    {
        $user = $this->getUserById($id);
        $user->setStatus(User::STATUS_SUSPENDED);
        
        return $this->userRepository->save($user);
    }

    public function getUserStats(): array
    {
        return [
            'by_role' => $this->userRepository->getStatsByRole(),
            'by_status' => $this->userRepository->getStatsByStatus(),
            'total' => $this->userRepository->count(),
        ];
    }

    public function getUsersByRole(string $role, int $limit = 100, int $offset = 0): array
    {
        return $this->userRepository->findByRole($role, $limit, $offset);
    }

    public function getUsersByStatus(string $status, int $limit = 100, int $offset = 0): array
    {
        return $this->userRepository->findByStatus($status, $limit, $offset);
    }

    public function searchUsers(string $query, int $limit = 100, int $offset = 0): array
    {
        return $this->userRepository->searchUsers($query, $limit, $offset);
    }
}