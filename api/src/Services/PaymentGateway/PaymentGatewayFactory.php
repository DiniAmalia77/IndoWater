<?php

declare(strict_types=1);

namespace IndoWater\Api\Services\PaymentGateway;

use IndoWater\Api\Exceptions\PaymentException;
use Psr\Log\LoggerInterface;

class PaymentGatewayFactory
{
    private LoggerInterface $logger;
    private array $config;

    public function __construct(LoggerInterface $logger, array $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Create payment gateway service instance
     */
    public function create(string $gateway): PaymentGatewayInterface
    {
        switch (strtolower($gateway)) {
            case 'midtrans':
                return new MidtransService(
                    $this->logger,
                    $this->config['midtrans'] ?? []
                );
                
            case 'doku':
                return new DokuService(
                    $this->logger,
                    $this->config['doku'] ?? []
                );
                
            default:
                throw new PaymentException("Unsupported payment gateway: {$gateway}");
        }
    }

    /**
     * Get available payment gateways
     */
    public function getAvailableGateways(): array
    {
        return ['midtrans', 'doku'];
    }

    /**
     * Check if gateway is available
     */
    public function isGatewayAvailable(string $gateway): bool
    {
        return in_array(strtolower($gateway), $this->getAvailableGateways());
    }
}