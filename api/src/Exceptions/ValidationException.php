<?php

declare(strict_types=1);

namespace IndoWater\Api\Exceptions;

class ValidationException extends \Exception
{
    private array $errors = [];

    public function __construct(string $message = 'Validation failed', array $errors = [], int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function addError(string $field, string $message): self
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
        return $this;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}