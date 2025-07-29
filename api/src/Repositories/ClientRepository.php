<?php

declare(strict_types=1);

namespace IndoWater\Api\Repositories;

use IndoWater\Api\Models\Client;

class ClientRepository extends BaseRepository
{
    protected string $table = 'clients';
    protected string $modelClass = Client::class;

    public function findByUserId(string $userId): ?Client
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findByStatus(string $status, int $limit = 100, int $offset = 0): array
    {
        return $this->findAll(['status' => $status], $limit, $offset);
    }

    public function findActiveClients(int $limit = 100, int $offset = 0): array
    {
        return $this->findByStatus(Client::STATUS_ACTIVE, $limit, $offset);
    }

    public function searchClients(string $query, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL 
                AND (company_name LIKE :query OR contact_person LIKE :query OR contact_email LIKE :query)
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

    public function getTotalRevenue(string $clientId, ?string $startDate = null, ?string $endDate = null): float
    {
        $sql = "SELECT COALESCE(SUM(sf.amount), 0) as total_revenue 
                FROM service_fees sf 
                INNER JOIN payments p ON sf.payment_id = p.id 
                WHERE sf.client_id = :client_id 
                AND p.status = 'success'
                AND sf.deleted_at IS NULL";
        
        $params = [':client_id' => $clientId];

        if ($startDate) {
            $sql .= " AND p.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND p.created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }

    public function getCustomerCount(string $clientId): int
    {
        $sql = "SELECT COUNT(*) FROM customers WHERE client_id = :client_id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':client_id', $clientId);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getMeterCount(string $clientId): int
    {
        $sql = "SELECT COUNT(*) FROM meters m 
                INNER JOIN customers c ON m.customer_id = c.id 
                WHERE c.client_id = :client_id 
                AND m.deleted_at IS NULL 
                AND c.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':client_id', $clientId);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getPropertyCount(string $clientId): int
    {
        $sql = "SELECT COUNT(*) FROM properties WHERE client_id = :client_id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':client_id', $clientId);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function updateServiceFee(string $clientId, string $type, float $value): bool
    {
        $sql = "UPDATE {$this->table} 
                SET service_fee_type = :type, service_fee_value = :value, updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':id', $clientId);
        
        return $stmt->execute();
    }
}