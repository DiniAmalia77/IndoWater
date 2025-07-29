<?php

declare(strict_types=1);

namespace IndoWater\Api\Models;

class Client extends BaseModel
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUSPENDED = 'suspended';

    public const SERVICE_FEE_PERCENTAGE = 'percentage';
    public const SERVICE_FEE_FIXED = 'fixed';

    protected string $userId;
    protected string $companyName;
    protected string $address;
    protected string $city;
    protected string $province;
    protected string $postalCode;
    protected string $contactPerson;
    protected string $contactEmail;
    protected string $contactPhone;
    protected ?string $logo = null;
    protected ?string $website = null;
    protected ?string $taxId = null;
    protected string $serviceFeeType = self::SERVICE_FEE_PERCENTAGE;
    protected float $serviceFeeValue = 5.00;
    protected string $status = self::STATUS_PENDING;

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): self
    {
        $this->companyName = $companyName;
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

    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(string $contactPerson): self
    {
        $this->contactPerson = $contactPerson;
        return $this;
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): self
    {
        $this->contactEmail = strtolower(trim($contactEmail));
        return $this;
    }

    public function getContactPhone(): string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(string $contactPhone): self
    {
        $this->contactPhone = $contactPhone;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;
        return $this;
    }

    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    public function setTaxId(?string $taxId): self
    {
        $this->taxId = $taxId;
        return $this;
    }

    public function getServiceFeeType(): string
    {
        return $this->serviceFeeType;
    }

    public function setServiceFeeType(string $serviceFeeType): self
    {
        if (!in_array($serviceFeeType, [self::SERVICE_FEE_PERCENTAGE, self::SERVICE_FEE_FIXED])) {
            throw new \InvalidArgumentException('Invalid service fee type');
        }
        $this->serviceFeeType = $serviceFeeType;
        return $this;
    }

    public function getServiceFeeValue(): float
    {
        return $this->serviceFeeValue;
    }

    public function setServiceFeeValue(float $serviceFeeValue): self
    {
        if ($serviceFeeValue < 0) {
            throw new \InvalidArgumentException('Service fee value cannot be negative');
        }
        $this->serviceFeeValue = $serviceFeeValue;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_PENDING, self::STATUS_SUSPENDED])) {
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

    public function suspend(): self
    {
        $this->status = self::STATUS_SUSPENDED;
        return $this;
    }

    public function calculateServiceFee(float $amount): float
    {
        if ($this->serviceFeeType === self::SERVICE_FEE_PERCENTAGE) {
            return $amount * ($this->serviceFeeValue / 100);
        }
        
        return $this->serviceFeeValue;
    }
}