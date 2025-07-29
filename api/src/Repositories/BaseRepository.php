<?php

declare(strict_types=1);

namespace IndoWater\Api\Repositories;

use IndoWater\Api\Models\BaseModel;
use IndoWater\Api\Utils\Database;
use PDO;
use PDOStatement;
use DateTime;

abstract class BaseRepository
{
    protected PDO $db;
    protected string $table;
    protected string $modelClass;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(string $id): ?BaseModel
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $field => $value) {
                $conditions[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        while ($data = $stmt->fetch()) {
            $results[] = $this->hydrate($data);
        }

        return $results;
    }

    public function count(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $field => $value) {
                $conditions[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function save(BaseModel $model): BaseModel
    {
        if ($this->exists($model->getId())) {
            return $this->update($model);
        }

        return $this->insert($model);
    }

    protected function insert(BaseModel $model): BaseModel
    {
        $data = $this->dehydrate($model);
        $fields = array_keys($data);
        $placeholders = array_map(fn($field) => ":{$field}", $fields);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();

        return $model;
    }

    protected function update(BaseModel $model): BaseModel
    {
        $model->touch();
        $data = $this->dehydrate($model);
        $id = $data['id'];
        unset($data['id'], $data['created_at']);

        $setClause = array_map(fn($field) => "{$field} = :{$field}", array_keys($data));

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $model;
    }

    public function delete(string $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function hardDelete(string $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function exists(string $id): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    protected function hydrate(array $data): BaseModel
    {
        $model = new $this->modelClass();
        
        foreach ($data as $key => $value) {
            $property = $this->snakeToCamel($key);
            $setter = 'set' . ucfirst($property);
            
            if (method_exists($model, $setter)) {
                if (in_array($key, ['created_at', 'updated_at', 'deleted_at', 'email_verified_at', 'last_login_at']) && $value !== null) {
                    $value = new DateTime($value);
                }
                
                $model->$setter($value);
            }
        }

        return $model;
    }

    protected function dehydrate(BaseModel $model): array
    {
        $data = [];
        $reflection = new \ReflectionClass($model);
        
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($model);
            $key = $this->camelToSnake($property->getName());
            
            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
            
            $data[$key] = $value;
        }

        return $data;
    }

    protected function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    protected function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace('_', '', ucwords($input, '_')));
    }

    protected function executeQuery(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt;
    }
}