<?php

declare(strict_types=1);

namespace IndoWater\Api\Repositories;

use IndoWater\Api\Models\User;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';
    protected string $modelClass = User::class;

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findByRole(string $role, int $limit = 100, int $offset = 0): array
    {
        return $this->findAll(['role' => $role], $limit, $offset);
    }

    public function findByStatus(string $status, int $limit = 100, int $offset = 0): array
    {
        return $this->findAll(['status' => $status], $limit, $offset);
    }

    public function emailExists(string $email, ?string $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email AND deleted_at IS NULL";
        $params = [':email' => $email];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    public function updateLastLogin(string $userId): bool
    {
        $sql = "UPDATE {$this->table} SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    public function markEmailAsVerified(string $userId): bool
    {
        $sql = "UPDATE {$this->table} SET email_verified_at = NOW(), updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    public function updatePassword(string $userId, string $hashedPassword): bool
    {
        $sql = "UPDATE {$this->table} SET password = :password, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    public function updateRememberToken(string $userId, ?string $token): bool
    {
        $sql = "UPDATE {$this->table} SET remember_token = :token, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    public function getStatsByRole(): array
    {
        $sql = "SELECT role, COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL GROUP BY role";
        $stmt = $this->db->query($sql);
        
        $stats = [];
        while ($row = $stmt->fetch()) {
            $stats[$row['role']] = (int) $row['count'];
        }
        
        return $stats;
    }

    public function getStatsByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL GROUP BY status";
        $stmt = $this->db->query($sql);
        
        $stats = [];
        while ($row = $stmt->fetch()) {
            $stats[$row['status']] = (int) $row['count'];
        }
        
        return $stats;
    }

    public function searchUsers(string $query, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL 
                AND (name LIKE :query OR email LIKE :query OR phone LIKE :query)
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $searchQuery = "%{$query}%";
        $stmt->bindValue(':query', $searchQuery);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        while ($data = $stmt->fetch()) {
            $results[] = $this->hydrate($data);
        }

        return $results;
    }
}