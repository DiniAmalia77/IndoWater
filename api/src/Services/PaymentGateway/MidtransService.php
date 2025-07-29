<?php

declare(strict_types=1);

namespace IndoWater\Api\Services\PaymentGateway;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Notification;
use IndoWater\Api\Exceptions\PaymentException;
use Psr\Log\LoggerInterface;

class MidtransService implements PaymentGatewayInterface
{
    private LoggerInterface $logger;
    private array $config;

    public function __construct(LoggerInterface $logger, array $config)
    {
        $this->logger = $logger;
        $this->config = $config;
        
        $this->initializeConfig();
    }

    private function initializeConfig(): void
    {
        Config::$serverKey = $this->config['server_key'];
        Config::$clientKey = $this->config['client_key'];
        Config::$isProduction = $this->config['is_production'] ?? false;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create payment transaction
     */
    public function createTransaction(array $transactionData): array
    {
        try {
            $this->logger->info('Creating Midtrans transaction', ['data' => $transactionData]);
            
            $snapToken = Snap::getSnapToken($transactionData);
            
            $this->logger->info('Midtrans transaction created successfully', [
                'order_id' => $transactionData['transaction_details']['order_id'],
                'snap_token' => $snapToken
            ]);
            
            return [
                'snap_token' => $snapToken,
                'redirect_url' => $this->getSnapUrl($snapToken)
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Midtrans transaction', [
                'error' => $e->getMessage(),
                'data' => $transactionData
            ]);
            
            throw new PaymentException('Failed to create payment transaction: ' . $e->getMessage());
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $orderId): array
    {
        try {
            $this->logger->info('Getting Midtrans transaction status', ['order_id' => $orderId]);
            
            $status = Transaction::status($orderId);
            
            $this->logger->info('Midtrans transaction status retrieved', [
                'order_id' => $orderId,
                'status' => $status
            ]);
            
            return $this->normalizeTransactionStatus($status);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Midtrans transaction status', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            
            throw new PaymentException('Failed to get transaction status: ' . $e->getMessage());
        }
    }

    /**
     * Handle webhook notification
     */
    public function handleNotification(array $notificationData): array
    {
        try {
            $this->logger->info('Processing Midtrans notification', ['data' => $notificationData]);
            
            $notification = new Notification();
            
            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? null;
            
            $this->logger->info('Midtrans notification processed', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus
            ]);
            
            return $this->normalizeNotificationData($notification);
        } catch (\Exception $e) {
            $this->logger->error('Failed to process Midtrans notification', [
                'error' => $e->getMessage(),
                'data' => $notificationData
            ]);
            
            throw new PaymentException('Failed to process notification: ' . $e->getMessage());
        }
    }

    /**
     * Build transaction data for credit purchase
     */
    public function buildCreditPurchaseTransaction(
        string $orderId,
        float $amount,
        array $customerData,
        array $itemDetails
    ): array {
        return [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount
            ],
            'customer_details' => [
                'first_name' => $customerData['first_name'],
                'last_name' => $customerData['last_name'] ?? '',
                'email' => $customerData['email'],
                'phone' => $customerData['phone'] ?? '',
                'billing_address' => [
                    'first_name' => $customerData['first_name'],
                    'last_name' => $customerData['last_name'] ?? '',
                    'address' => $customerData['address'] ?? '',
                    'city' => $customerData['city'] ?? '',
                    'postal_code' => $customerData['postal_code'] ?? '',
                    'phone' => $customerData['phone'] ?? '',
                    'country_code' => 'IDN'
                ]
            ],
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => $this->config['finish_url'] ?? null,
                'unfinish' => $this->config['unfinish_url'] ?? null,
                'error' => $this->config['error_url'] ?? null
            ]
        ];
    }

    private function getSnapUrl(string $snapToken): string
    {
        $baseUrl = Config::$isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
            
        return $baseUrl . '/' . $snapToken;
    }

    private function normalizeTransactionStatus($status): array
    {
        return [
            'order_id' => $status->order_id ?? null,
            'transaction_id' => $status->transaction_id ?? null,
            'transaction_status' => $status->transaction_status ?? null,
            'fraud_status' => $status->fraud_status ?? null,
            'payment_type' => $status->payment_type ?? null,
            'gross_amount' => $status->gross_amount ?? null,
            'transaction_time' => $status->transaction_time ?? null,
            'settlement_time' => $status->settlement_time ?? null,
            'status_code' => $status->status_code ?? null,
            'status_message' => $status->status_message ?? null,
            'raw_data' => $status
        ];
    }

    private function normalizeNotificationData($notification): array
    {
        return [
            'order_id' => $notification->order_id,
            'transaction_id' => $notification->transaction_id ?? null,
            'transaction_status' => $notification->transaction_status,
            'fraud_status' => $notification->fraud_status ?? null,
            'payment_type' => $notification->payment_type ?? null,
            'gross_amount' => $notification->gross_amount ?? null,
            'transaction_time' => $notification->transaction_time ?? null,
            'settlement_time' => $notification->settlement_time ?? null,
            'status_code' => $notification->status_code ?? null,
            'status_message' => $notification->status_message ?? null,
            'signature_key' => $notification->signature_key ?? null,
            'raw_data' => $notification
        ];
    }
}