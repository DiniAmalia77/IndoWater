<?php

declare(strict_types=1);

namespace IndoWater\Api\Services;

use IndoWater\Api\Models\Payment;
use IndoWater\Api\Models\Credit;
use IndoWater\Api\Models\Customer;
use IndoWater\Api\Repositories\PaymentRepository;
use IndoWater\Api\Repositories\CreditRepository;
use IndoWater\Api\Repositories\CustomerRepository;
use IndoWater\Api\Services\PaymentGateway\PaymentGatewayFactory;
use IndoWater\Api\Services\PaymentGateway\PaymentGatewayInterface;
use IndoWater\Api\Exceptions\PaymentException;
use IndoWater\Api\Exceptions\ValidationException;
use IndoWater\Api\Exceptions\NotFoundException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class PaymentService
{
    private PaymentRepository $paymentRepository;
    private CreditRepository $creditRepository;
    private CustomerRepository $customerRepository;
    private PaymentGatewayFactory $gatewayFactory;
    private LoggerInterface $logger;

    public function __construct(
        PaymentRepository $paymentRepository,
        CreditRepository $creditRepository,
        CustomerRepository $customerRepository,
        PaymentGatewayFactory $gatewayFactory,
        LoggerInterface $logger
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->creditRepository = $creditRepository;
        $this->customerRepository = $customerRepository;
        $this->gatewayFactory = $gatewayFactory;
        $this->logger = $logger;
    }

    /**
     * Create credit purchase payment
     */
    public function createCreditPurchase(array $data): array
    {
        $this->validateCreditPurchaseData($data);

        try {
            // Get customer
            $customer = $this->customerRepository->findById($data['customer_id']);
            if (!$customer) {
                throw new NotFoundException('Customer not found');
            }

            // Generate order ID
            $orderId = $this->generateOrderId();

            // Create payment record
            $payment = new Payment();
            $payment->setId(Uuid::uuid4()->toString());
            $payment->setOrderId($orderId);
            $payment->setCustomerId($customer->getId());
            $payment->setAmount($data['amount']);
            $payment->setType('credit_purchase');
            $payment->setGateway($data['gateway']);
            $payment->setStatus('pending');
            $payment->setMetadata([
                'credit_amount' => $data['credit_amount'],
                'denomination' => $data['denomination'] ?? null,
                'customer_data' => $this->buildCustomerData($customer),
                'item_details' => $this->buildItemDetails($data)
            ]);

            $this->paymentRepository->save($payment);

            // Create payment gateway transaction
            $gateway = $this->gatewayFactory->create($data['gateway']);
            $customerData = $this->buildCustomerData($customer);
            $itemDetails = $this->buildItemDetails($data);

            $transactionData = $gateway->buildCreditPurchaseTransaction(
                $orderId,
                $data['amount'],
                $customerData,
                $itemDetails
            );

            $gatewayResponse = $gateway->createTransaction($transactionData);

            // Update payment with gateway response
            $payment->setGatewayTransactionId($gatewayResponse['transaction_id'] ?? null);
            $payment->setGatewayResponse($gatewayResponse);
            $this->paymentRepository->save($payment);

            $this->logger->info('Credit purchase payment created', [
                'payment_id' => $payment->getId(),
                'order_id' => $orderId,
                'customer_id' => $customer->getId(),
                'amount' => $data['amount'],
                'gateway' => $data['gateway']
            ]);

            return [
                'payment_id' => $payment->getId(),
                'order_id' => $orderId,
                'amount' => $data['amount'],
                'status' => $payment->getStatus(),
                'gateway_response' => $gatewayResponse
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to create credit purchase payment', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            throw new PaymentException('Failed to create payment: ' . $e->getMessage());
        }
    }

    /**
     * Process payment notification/webhook
     */
    public function processNotification(string $gateway, array $notificationData): array
    {
        try {
            $this->logger->info('Processing payment notification', [
                'gateway' => $gateway,
                'data' => $notificationData
            ]);

            $gatewayService = $this->gatewayFactory->create($gateway);
            $normalizedData = $gatewayService->handleNotification($notificationData);

            // Find payment by order ID
            $payment = $this->paymentRepository->findByOrderId($normalizedData['order_id']);
            if (!$payment) {
                throw new NotFoundException('Payment not found for order ID: ' . $normalizedData['order_id']);
            }

            // Update payment status based on gateway response
            $this->updatePaymentStatus($payment, $normalizedData);

            // If payment is successful, process credit addition
            if ($payment->getStatus() === 'completed') {
                $this->processCreditAddition($payment);
            }

            $this->logger->info('Payment notification processed successfully', [
                'payment_id' => $payment->getId(),
                'order_id' => $payment->getOrderId(),
                'status' => $payment->getStatus()
            ]);

            return [
                'payment_id' => $payment->getId(),
                'order_id' => $payment->getOrderId(),
                'status' => $payment->getStatus(),
                'processed' => true
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to process payment notification', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'data' => $notificationData
            ]);

            throw new PaymentException('Failed to process notification: ' . $e->getMessage());
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId): array
    {
        $payment = $this->paymentRepository->findById($paymentId);
        if (!$payment) {
            throw new NotFoundException('Payment not found');
        }

        try {
            // Check status from gateway
            $gateway = $this->gatewayFactory->create($payment->getGateway());
            $gatewayStatus = $gateway->getTransactionStatus($payment->getOrderId());

            // Update local payment status if needed
            $this->updatePaymentStatus($payment, $gatewayStatus);

            return [
                'payment_id' => $payment->getId(),
                'order_id' => $payment->getOrderId(),
                'status' => $payment->getStatus(),
                'amount' => $payment->getAmount(),
                'gateway' => $payment->getGateway(),
                'gateway_status' => $gatewayStatus,
                'created_at' => $payment->getCreatedAt(),
                'updated_at' => $payment->getUpdatedAt()
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get payment status', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return [
                'payment_id' => $payment->getId(),
                'order_id' => $payment->getOrderId(),
                'status' => $payment->getStatus(),
                'amount' => $payment->getAmount(),
                'gateway' => $payment->getGateway(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        return [
            'midtrans' => [
                'name' => 'Midtrans',
                'methods' => [
                    'credit_card' => 'Credit Card',
                    'bank_transfer' => 'Bank Transfer',
                    'e_wallet' => 'E-Wallet',
                    'convenience_store' => 'Convenience Store'
                ]
            ],
            'doku' => [
                'name' => 'DOKU',
                'methods' => [
                    'virtual_account' => 'Virtual Account',
                    'credit_card' => 'Credit Card',
                    'e_wallet' => 'E-Wallet'
                ]
            ]
        ];
    }

    private function validateCreditPurchaseData(array $data): void
    {
        $required = ['customer_id', 'amount', 'credit_amount', 'gateway'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new ValidationException("Field '{$field}' is required");
            }
        }

        if (!$this->gatewayFactory->isGatewayAvailable($data['gateway'])) {
            throw new ValidationException('Invalid payment gateway');
        }

        if ($data['amount'] <= 0) {
            throw new ValidationException('Amount must be greater than 0');
        }

        if ($data['credit_amount'] <= 0) {
            throw new ValidationException('Credit amount must be greater than 0');
        }
    }

    private function generateOrderId(): string
    {
        return 'IW-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
    }

    private function buildCustomerData(Customer $customer): array
    {
        return [
            'first_name' => $customer->getFirstName(),
            'last_name' => $customer->getLastName(),
            'email' => $customer->getEmail(),
            'phone' => $customer->getPhone(),
            'address' => $customer->getAddress(),
            'city' => $customer->getCity(),
            'postal_code' => $customer->getPostalCode()
        ];
    }

    private function buildItemDetails(array $data): array
    {
        return [
            [
                'id' => 'credit_' . ($data['denomination'] ?? 'custom'),
                'name' => 'Water Credit - ' . number_format($data['credit_amount']) . ' units',
                'price' => $data['amount'],
                'quantity' => 1,
                'category' => 'water_credit'
            ]
        ];
    }

    private function updatePaymentStatus(Payment $payment, array $gatewayData): void
    {
        $currentStatus = $payment->getStatus();
        $newStatus = $this->mapGatewayStatusToPaymentStatus($gatewayData);

        if ($currentStatus !== $newStatus) {
            $payment->setStatus($newStatus);
            $payment->setGatewayResponse($gatewayData);
            $this->paymentRepository->save($payment);

            $this->logger->info('Payment status updated', [
                'payment_id' => $payment->getId(),
                'old_status' => $currentStatus,
                'new_status' => $newStatus
            ]);
        }
    }

    private function mapGatewayStatusToPaymentStatus(array $gatewayData): string
    {
        $transactionStatus = strtolower($gatewayData['transaction_status'] ?? '');
        
        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
            case 'success':
            case 'completed':
                return 'completed';
            case 'pending':
                return 'pending';
            case 'deny':
            case 'cancel':
            case 'expire':
            case 'failure':
            case 'failed':
                return 'failed';
            default:
                return 'pending';
        }
    }

    private function processCreditAddition(Payment $payment): void
    {
        $metadata = $payment->getMetadata();
        $creditAmount = $metadata['credit_amount'] ?? 0;

        if ($creditAmount > 0) {
            $credit = new Credit();
            $credit->setId(Uuid::uuid4()->toString());
            $credit->setCustomerId($payment->getCustomerId());
            $credit->setAmount($creditAmount);
            $credit->setType('purchase');
            $credit->setSource('payment');
            $credit->setSourceId($payment->getId());
            $credit->setDescription('Credit purchase via ' . $payment->getGateway());
            $credit->setStatus('active');

            $this->creditRepository->save($credit);

            $this->logger->info('Credit added to customer account', [
                'customer_id' => $payment->getCustomerId(),
                'credit_amount' => $creditAmount,
                'payment_id' => $payment->getId()
            ]);
        }
    }
}