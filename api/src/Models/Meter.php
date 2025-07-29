<?php

declare(strict_types=1);

namespace IndoWater\Api\Models;

use DateTime;

class Meter extends BaseModel
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_DISCONNECTED = 'disconnected';

    protected string $meterId;
    protected string $customerId;
    protected string $propertyId;
    protected DateTime $installationDate;
    protected string $meterType;
    protected string $meterModel;
    protected string $meterSerial;
    protected string $firmwareVersion;
    protected string $hardwareVersion;
    protected ?string $locationDescription = null;
    protected ?float $latitude = null;
    protected ?float $longitude = null;
    protected string $status = self::STATUS_ACTIVE;
    protected float $lastReading = 0.0;
    protected ?DateTime $lastReadingAt = null;
    protected float $lastCredit = 0.0;
    protected ?DateTime $lastCreditAt = null;

    public function getMeterId(): string
    {
        return $this->meterId;
    }

    public function setMeterId(string $meterId): self
    {
        $this->meterId = $meterId;
        return $this;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): self
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function getPropertyId(): string
    {
        return $this->propertyId;
    }

    public function setPropertyId(string $propertyId): self
    {
        $this->propertyId = $propertyId;
        return $this;
    }

    public function getInstallationDate(): DateTime
    {
        return $this->installationDate;
    }

    public function setInstallationDate(DateTime $installationDate): self
    {
        $this->installationDate = $installationDate;
        return $this;
    }

    public function getMeterType(): string
    {
        return $this->meterType;
    }

    public function setMeterType(string $meterType): self
    {
        $this->meterType = $meterType;
        return $this;
    }

    public function getMeterModel(): string
    {
        return $this->meterModel;
    }

    public function setMeterModel(string $meterModel): self
    {
        $this->meterModel = $meterModel;
        return $this;
    }

    public function getMeterSerial(): string
    {
        return $this->meterSerial;
    }

    public function setMeterSerial(string $meterSerial): self
    {
        $this->meterSerial = $meterSerial;
        return $this;
    }

    public function getFirmwareVersion(): string
    {
        return $this->firmwareVersion;
    }

    public function setFirmwareVersion(string $firmwareVersion): self
    {
        $this->firmwareVersion = $firmwareVersion;
        return $this;
    }

    public function getHardwareVersion(): string
    {
        return $this->hardwareVersion;
    }

    public function setHardwareVersion(string $hardwareVersion): self
    {
        $this->hardwareVersion = $hardwareVersion;
        return $this;
    }

    public function getLocationDescription(): ?string
    {
        return $this->locationDescription;
    }

    public function setLocationDescription(?string $locationDescription): self
    {
        $this->locationDescription = $locationDescription;
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
        $allowedStatuses = [
            self::STATUS_ACTIVE, self::STATUS_INACTIVE, 
            self::STATUS_MAINTENANCE, self::STATUS_DISCONNECTED
        ];
        
        if (!in_array($status, $allowedStatuses)) {
            throw new \InvalidArgumentException('Invalid status');
        }
        
        $this->status = $status;
        return $this;
    }

    public function getLastReading(): float
    {
        return $this->lastReading;
    }

    public function setLastReading(float $lastReading): self
    {
        $this->lastReading = $lastReading;
        return $this;
    }

    public function getLastReadingAt(): ?DateTime
    {
        return $this->lastReadingAt;
    }

    public function setLastReadingAt(?DateTime $lastReadingAt): self
    {
        $this->lastReadingAt = $lastReadingAt;
        return $this;
    }

    public function getLastCredit(): float
    {
        return $this->lastCredit;
    }

    public function setLastCredit(float $lastCredit): self
    {
        $this->lastCredit = $lastCredit;
        return $this;
    }

    public function getLastCreditAt(): ?DateTime
    {
        return $this->lastCreditAt;
    }

    public function setLastCreditAt(?DateTime $lastCreditAt): self
    {
        $this->lastCreditAt = $lastCreditAt;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isOnline(): bool
    {
        if (!$this->lastReadingAt) {
            return false;
        }

        // Consider meter online if last reading was within 1 hour
        $oneHourAgo = new DateTime('-1 hour');
        return $this->lastReadingAt > $oneHourAgo;
    }

    public function hasLowCredit(float $threshold = 50000.0): bool
    {
        return $this->lastCredit < $threshold;
    }

    public function updateReading(float $reading): self
    {
        $this->lastReading = $reading;
        $this->lastReadingAt = new DateTime();
        return $this;
    }

    public function updateCredit(float $credit): self
    {
        $this->lastCredit = $credit;
        $this->lastCreditAt = new DateTime();
        return $this;
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

    public function getDaysSinceInstallation(): int
    {
        $now = new DateTime();
        $diff = $now->diff($this->installationDate);
        return $diff->days;
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'green',
            self::STATUS_INACTIVE => 'gray',
            self::STATUS_MAINTENANCE => 'yellow',
            self::STATUS_DISCONNECTED => 'red',
            default => 'gray',
        };
    }
}