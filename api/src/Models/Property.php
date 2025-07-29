<?php

declare(strict_types=1);

namespace IndoWater\Api\Models;

class Property extends BaseModel
{
    public const TYPE_RESIDENTIAL = 'residential';
    public const TYPE_COMMERCIAL = 'commercial';
    public const TYPE_INDUSTRIAL = 'industrial';
    public const TYPE_DORMITORY = 'dormitory';
    public const TYPE_RENTAL = 'rental';
    public const TYPE_OTHER = 'other';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected string $clientId;
    protected string $name;
    protected string $type = self::TYPE_RESIDENTIAL;
    protected string $address;
    protected string $city;
    protected string $province;
    protected string $postalCode;
    protected ?float $latitude = null;
    protected ?float $longitude = null;
    protected string $status = self::STATUS_ACTIVE;

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $allowedTypes = [
            self::TYPE_RESIDENTIAL, self::TYPE_COMMERCIAL, self::TYPE_INDUSTRIAL,
            self::TYPE_DORMITORY, self::TYPE_RENTAL, self::TYPE_OTHER
        ];
        
        if (!in_array($type, $allowedTypes)) {
            throw new \InvalidArgumentException('Invalid property type');
        }
        
        $this->type = $type;
        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getProvince(): string
    {
        return $this->province;
    }

    public function setProvince(string $province): self
    {
        $this->province = $province;
        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, [self::STATUS_ACTIVE, self::STATUS_INACTIVE])) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $this->status = $status;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function activate(): self
    {
        $this->status = self::STATUS_ACTIVE;
        return $this;
    }

    public function deactivate(): self
    {
        $this->status = self::STATUS_INACTIVE;
        return $this;
    }

    public function getFullAddress(): string
    {
        return $this->address . ', ' . $this->city . ', ' . $this->province . ' ' . $this->postalCode;
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function getCoordinates(): ?array
    {
        if (!$this->hasCoordinates()) {
            return null;
        }

        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function setCoordinates(?float $latitude, ?float $longitude): self
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        return $this;
    }
}