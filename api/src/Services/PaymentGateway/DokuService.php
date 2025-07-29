<?php

declare(strict_types=1);

namespace IndoWater\Api\Services\PaymentGateway;

use Doku\Snap\Snap;
use Doku\Snap\Models\Payment\PaymentRequestV1;
use Doku\Snap\Models\Payment\PaymentDirectRequestV1;
use Doku\Snap\Models\Utilities\AdditionalInfo;
use Doku\Snap\Models\Utilities\TotalAmount;
use Doku\Snap\Models\Utilities\LineItems;
use Doku\Snap\Models\Utilities\Customer;
use IndoWater\Api\Exceptions\PaymentException;
use Psr\Log\LoggerInterface;

class DokuService implements PaymentGatewayInterface
{
    private LoggerInterface $logger;
    private array $config;
    private Snap $snap;

    public function __construct(LoggerInterface $logger, array $config)
    {
        $this->logger = $logger;
        $this->config = $config;
        
        $this->initializeSnap();
    }

    private function initializeSnap(): void
    {
        $this->snap = new Snap([
            'isProduction' => $this->config['is_production'] ?? false,
            'clientId' => $this->config['client_id'],
            'secret' => $this->config['secret'],
            'privateKey' => $this->config['private_key'],
            'publicKey' => $this->config['public_key'],
            'issuer' => $this->config['issuer'] ?? 'doku',
            'authCode' => $this->config['auth_code'] ?? null
        ]);
    }

    /**
     * Create payment transaction
     */
    public function createTransaction(array $transactionData): array
    {
        return $this->createPayment($transactionData);
    }

    /**
     * Create payment transaction
     */
    public function createPayment(array $paymentData): array
    {
        try {
            $this->logger->info('Creating DOKU payment', ['data' => $paymentData]);
            
            $paymentRequest = $this->buildPaymentRequest($paymentData);
            $response = $this->snap->createPayment($paymentRequest);
            
            $this->logger->info('DOKU payment created successfully', [
                'order_id' => $paymentData['order_id'],
                'response' => $response
            ]);
            
            return [
                'payment_url' => $response->paymentUrl ?? null,
                'virtual_account_info' => $response->virtualAccountInfo ?? null,
                'additional_info' => $response->additionalInfo ?? null,
                'response_code' => $response->responseCode ?? null,
                'response_message' => $response->responseMessage ?? null,
                'raw_response' => $response
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to create DOKU payment', [
                'error' => $e->getMessage(),
                'data' => $paymentData
            ]);
            
            throw new PaymentException('Failed to create payment: ' . $e->getMessage());
        }
    }

    /**
     * Create direct payment (for specific payment channels)
     */
    public function createDirectPayment(array $paymentData): array
    {
        try {
            $this->logger->info('Creating DOKU direct payment', ['data' => $paymentData]);
            
            $paymentRequest = $this->buildDirectPaymentRequest($paymentData);
            $response = $this->snap->createDirectPayment($paymentRequest);
            
            $this->logger->info('DOKU direct payment created successfully', [
                'order_id' => $paymentData['order_id'],
                'response' => $response
            ]);
            
            return [
                'payment_url' => $response->paymentUrl ?? null,
                'virtual_account_info' => $response->virtualAccountInfo ?? null,
                'additional_info' => $response->additionalInfo ?? null,
                'response_code' => $response->responseCode ?? null,
                'response_message' => $response->responseMessage ?? null,
                'raw_response' => $response
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to create DOKU direct payment', [
                'error' => $e->getMessage(),
                'data' => $paymentData
            ]);
            
            throw new PaymentException('Failed to create direct payment: ' . $e->getMessage());
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $orderId): array
    {
        return $this->checkPaymentStatus($orderId);
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $orderId): array
    {
        try {
            $this->logger->info('Checking DOKU payment status', ['order_id' => $orderId]);
            
            $response = $this->snap->checkStatus($orderId);
            
            $this->logger->info('DOKU payment status retrieved', [
                'order_id' => $orderId,
                'response' => $response
            ]);
            
            return [
                'order_id' => $response->orderId ?? null,
                'transaction_status' => $response->transactionStatus ?? null,
                'amount' => $response->amount ?? null,
                'payment_method' => $response->paymentMethod ?? null,
                'transaction_time' => $response->transactionTime ?? null,
                'response_code' => $response->responseCode ?? null,
                'response_message' => $response->responseMessage ?? null,
                'raw_response' => $response
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to check DOKU payment status', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            
            throw new PaymentException('Failed to check payment status: ' . $e->getMessage());
        }
    }

    /**
     * Handle webhook notification
     */
    public function handleNotification(array $notificationData): array
    {
        try {
            $this->logger->info('Processing DOKU notification', ['data' => $notificationData]);
            
            // Verify signature
            if (!$this->verifySignature($notificationData)) {
                throw new PaymentException('Invalid notification signature');
            }
            
            $normalizedData = [
                'order_id' => $notificationData['order']['invoice_number'] ?? null,
                'transaction_status' => $notificationData['transaction']['status'] ?? null,
                'amount' => $notificationData['order']['amount'] ?? null,
                'payment_method' => $notificationData['payment']['method'] ?? null,
                'transaction_time' => $notificationData['transaction']['date'] ?? null,
                'raw_data' => $notificationData
            ];
            
            $this->logger->info('DOKU notification processed', $normalizedData);
            
            return $normalizedData;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process DOKU notification', [
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
        return $this->buildCreditPurchasePayment($orderId, $amount, $customerData, $itemDetails);
    }

    /**
     * Build payment request for credit purchase
     */
    public function buildCreditPurchasePayment(
        string $orderId,
        float $amount,
        array $customerData,
        array $itemDetails
    ): array {
        return [
            'order_id' => $orderId,
            'amount' => $amount,
            'customer' => [
                'name' => $customerData['first_name'] . ' ' . ($customerData['last_name'] ?? ''),
                'email' => $customerData['email'],
                'phone' => $customerData['phone'] ?? '',
                'address' => $customerData['address'] ?? '',
                'city' => $customerData['city'] ?? '',
                'postal_code' => $customerData['postal_code'] ?? ''
            ],
            'items' => $itemDetails,
            'callback_url' => $this->config['callback_url'] ?? null,
            'redirect_url' => $this->config['redirect_url'] ?? null
        ];
    }

    private function buildPaymentRequest(array $paymentData): PaymentRequestV1
    {
        $totalAmount = new TotalAmount();
        $totalAmount->value = (string) $paymentData['amount'];
        $totalAmount->currency = 'IDR';

        $customer = new Customer();
        $customer->name = $paymentData['customer']['name'];
        $customer->email = $paymentData['customer']['email'];
        $customer->phone = $paymentData['customer']['phone'] ?? '';
        $customer->address = $paymentData['customer']['address'] ?? '';
        $customer->country = 'ID';

        $lineItems = [];
        foreach ($paymentData['items'] as $item) {
            $lineItem = new LineItems();
            $lineItem->name = $item['name'];
            $lineItem->price = (string) $item['price'];
            $lineItem->quantity = $item['quantity'];
            $lineItems[] = $lineItem;
        }

        $additionalInfo = new AdditionalInfo();
        $additionalInfo->channel = $paymentData['channel'] ?? 'VIRTUAL_ACCOUNT_BANK_CIMB';

        $paymentRequest = new PaymentRequestV1();
        $paymentRequest->order->invoiceNumber = $paymentData['order_id'];
        $paymentRequest->order->amount = $totalAmount;
        $paymentRequest->order->currency = 'IDR';
        $paymentRequest->order->callbackUrl = $paymentData['callback_url'] ?? $this->config['callback_url'];
        $paymentRequest->order->lineItems = $lineItems;
        $paymentRequest->payment->paymentDueDate = $paymentData['due_date'] ?? date('c', strtotime('+1 day'));
        $paymentRequest->customer = $customer;
        $paymentRequest->additionalInfo = $additionalInfo;

        return $paymentRequest;
    }

    private function buildDirectPaymentRequest(array $paymentData): PaymentDirectRequestV1
    {
        $totalAmount = new TotalAmount();
        $totalAmount->value = (string) $paymentData['amount'];
        $totalAmount->currency = 'IDR';

        $customer = new Customer();
        $customer->name = $paymentData['customer']['name'];
        $customer->email = $paymentData['customer']['email'];
        $customer->phone = $paymentData['customer']['phone'] ?? '';

        $additionalInfo = new AdditionalInfo();
        $additionalInfo->channel = $paymentData['channel'];

        $paymentRequest = new PaymentDirectRequestV1();
        $paymentRequest->order->invoiceNumber = $paymentData['order_id'];
        $paymentRequest->order->amount = $totalAmount;
        $paymentRequest->order->currency = 'IDR';
        $paymentRequest->order->callbackUrl = $paymentData['callback_url'] ?? $this->config['callback_url'];
        $paymentRequest->payment->paymentMethod = $paymentData['payment_method'];
        $paymentRequest->customer = $customer;
        $paymentRequest->additionalInfo = $additionalInfo;

        return $paymentRequest;
    }

    private function verifySignature(array $notificationData): bool
    {
        // Implement DOKU signature verification logic
        // This is a simplified version - implement according to DOKU documentation
        $signature = $notificationData['signature'] ?? '';
        $expectedSignature = $this->generateSignature($notificationData);
        
        return hash_equals($expectedSignature, $signature);
    }

    private function generateSignature(array $data): string
    {
        // Implement DOKU signature generation logic
        // This is a simplified version - implement according to DOKU documentation
        $stringToSign = implode('', [
            $data['order']['invoice_number'] ?? '',
            $data['order']['amount'] ?? '',
            $this->config['secret']
        ]);
        
        return hash('sha256', $stringToSign);
    }
}