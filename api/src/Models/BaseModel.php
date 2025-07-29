<?php

declare(strict_types=1);

namespace IndoWater\Api\Models;

use DateTime;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

abstract class BaseModel implements JsonSerializable
{
    protected string $id;
    protected DateTime $createdAt;
    protected DateTime $updatedAt;
    protected ?DateTime $deletedAt = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function softDelete(): self
    {
        $this->deletedAt = new DateTime();
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function restore(): self
    {
        $this->deletedAt = null;
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function touch(): self
    {
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function jsonSerialize(): array
    {
        $data = [];
        $reflection = new \ReflectionClass($this);
        
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);
            
            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
            
            $data[$this->camelToSnake($property->getName())] = $value;
        }
        
        return $data;
    }

    protected function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}