<?php

declare(strict_types=1);

namespace IndoWater\Api\Exceptions;

class PaymentException extends \Exception
{
    private ?array $details;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, ?array $details = null)
    {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function setDetails(array $details): void
    {
        $this->details = $details;
    }
}