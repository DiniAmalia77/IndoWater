<?php

declare(strict_types=1);

namespace IndoWater\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use IndoWater\Api\Exceptions\ValidationException;

abstract class BaseController
{
    protected function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    protected function validateRequired(array $data, array $required): void
    {
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new ValidationException(
                'Missing required fields: ' . implode(', ', $missing),
                array_fill_keys($missing, ['This field is required'])
            );
        }
    }

    protected function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }
    }

    protected function validateUuid(string $uuid): void
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)) {
            throw new ValidationException('Invalid UUID format');
        }
    }

    protected function validateNumeric(mixed $value, string $field): void
    {
        if (!is_numeric($value)) {
            throw new ValidationException("Field {$field} must be numeric");
        }
    }

    protected function validatePositive(mixed $value, string $field): void
    {
        $this->validateNumeric($value, $field);
        
        if ((float) $value <= 0) {
            throw new ValidationException("Field {$field} must be positive");
        }
    }

    protected function validateInArray(mixed $value, array $allowed, string $field): void
    {
        if (!in_array($value, $allowed)) {
            throw new ValidationException(
                "Field {$field} must be one of: " . implode(', ', $allowed)
            );
        }
    }

    protected function validateLength(string $value, int $min, int $max, string $field): void
    {
        $length = strlen($value);
        
        if ($length < $min || $length > $max) {
            throw new ValidationException(
                "Field {$field} must be between {$min} and {$max} characters"
            );
        }
    }

    protected function validateDate(string $date, string $field, string $format = 'Y-m-d'): void
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        
        if (!$dateTime || $dateTime->format($format) !== $date) {
            throw new ValidationException("Field {$field} must be a valid date in format {$format}");
        }
    }

    protected function getPaginationParams(array $queryParams): array
    {
        $page = max(1, (int) ($queryParams['page'] ?? 1));
        $limit = min(100, max(1, (int) ($queryParams['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    protected function getSearchParams(array $queryParams): array
    {
        return [
            'search' => $queryParams['search'] ?? '',
            'sort' => $queryParams['sort'] ?? 'created_at',
            'order' => in_array(strtolower($queryParams['order'] ?? 'desc'), ['asc', 'desc']) 
                ? strtolower($queryParams['order']) 
                : 'desc',
        ];
    }

    protected function getFilterParams(array $queryParams, array $allowedFilters): array
    {
        $filters = [];
        
        foreach ($allowedFilters as $filter) {
            if (isset($queryParams[$filter]) && !empty($queryParams[$filter])) {
                $filters[$filter] = $queryParams[$filter];
            }
        }
        
        return $filters;
    }

    protected function buildPaginatedResponse(array $items, int $total, array $pagination): array
    {
        $totalPages = ceil($total / $pagination['limit']);
        
        return [
            'data' => $items,
            'pagination' => [
                'current_page' => $pagination['page'],
                'per_page' => $pagination['limit'],
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $pagination['page'] < $totalPages,
                'has_prev' => $pagination['page'] > 1,
            ],
        ];
    }

    protected function sanitizeInput(array $data, array $allowed): array
    {
        return array_intersect_key($data, array_flip($allowed));
    }

    protected function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 2, ',', '.');
    }

    protected function formatDate(\DateTime $date, string $format = 'Y-m-d H:i:s'): string
    {
        return $date->format($format);
    }

    protected function parseFilters(array $queryParams): array
    {
        $filters = [];
        
        // Date range filters
        if (!empty($queryParams['start_date'])) {
            $filters['start_date'] = $queryParams['start_date'];
        }
        
        if (!empty($queryParams['end_date'])) {
            $filters['end_date'] = $queryParams['end_date'];
        }
        
        // Status filter
        if (!empty($queryParams['status'])) {
            $filters['status'] = $queryParams['status'];
        }
        
        return $filters;
    }
}