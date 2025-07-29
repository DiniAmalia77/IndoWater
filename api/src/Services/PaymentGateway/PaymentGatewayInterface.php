<?php

declare(strict_types=1);

namespace IndoWater\Api\Services\PaymentGateway;

interface PaymentGatewayInterface
{
    /**
     * Create payment transaction
     */
    public function createTransaction(array $transactionData): array;

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $orderId): array;

    /**
     * Handle webhook notification
     */
    public function handleNotification(array $notificationData): array;

    /**
     * Build transaction data for credit purchase
     */
    public function buildCreditPurchaseTransaction(
        string $orderId,
        float $amount,
        array $customerData,
        array $itemDetails
    ): array;
}