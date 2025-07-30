<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PaymentController
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getPayments(Request $request, Response $response): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client, superadmin, or customer
        if ($user['role'] !== 'client' && $user['role'] !== 'superadmin' && $user['role'] !== 'customer') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // In a real application, you would fetch payments from the database
        // For client users, you would only fetch payments belonging to their client account
        // For superadmin users, you would fetch all payments or filter by client ID if provided
        // For customer users, you would only fetch their own payments
        
        // For demo purposes, we'll return mock data
        $payments = [
            [
                'id' => 'P-101',
                'customer_id' => '1',
                'client_id' => '1',
                'invoice_number' => 'INV-2023-01-101',
                'amount' => 127500,
                'period' => 'January 2023',
                'due_date' => '2023-02-15',
                'payment_date' => '2023-02-05',
                'payment_method' => 'bank_transfer',
                'status' => 'completed',
                'customer' => [
                    'id' => '1',
                    'name' => 'John Doe',
                ],
            ],
            [
                'id' => 'P-102',
                'customer_id' => '1',
                'client_id' => '1',
                'invoice_number' => 'INV-2023-02-101',
                'amount' => 126500,
                'period' => 'February 2023',
                'due_date' => '2023-03-15',
                'payment_date' => '2023-03-07',
                'payment_method' => 'bank_transfer',
                'status' => 'completed',
                'customer' => [
                    'id' => '1',
                    'name' => 'John Doe',
                ],
            ],
            [
                'id' => 'P-103',
                'customer_id' => '1',
                'client_id' => '1',
                'invoice_number' => 'INV-2023-03-101',
                'amount' => 122000,
                'period' => 'March 2023',
                'due_date' => '2023-04-15',
                'payment_date' => '2023-04-05',
                'payment_method' => 'e_wallet',
                'status' => 'completed',
                'customer' => [
                    'id' => '1',
                    'name' => 'John Doe',
                ],
            ],
            [
                'id' => 'P-104',
                'customer_id' => '2',
                'client_id' => '1',
                'invoice_number' => 'INV-2023-01-102',
                'amount' => 130000,
                'period' => 'January 2023',
                'due_date' => '2023-02-15',
                'payment_date' => '2023-02-10',
                'payment_method' => 'credit_card',
                'status' => 'completed',
                'customer' => [
                    'id' => '2',
                    'name' => 'Jane Smith',
                ],
            ],
            [
                'id' => 'P-105',
                'customer_id' => '3',
                'client_id' => '1',
                'invoice_number' => 'INV-2023-01-103',
                'amount' => 115000,
                'period' => 'January 2023',
                'due_date' => '2023-02-15',
                'payment_date' => null,
                'payment_method' => null,
                'status' => 'pending',
                'customer' => [
                    'id' => '3',
                    'name' => 'Bob Johnson',
                ],
            ],
            [
                'id' => 'P-106',
                'customer_id' => '4',
                'client_id' => '2',
                'invoice_number' => 'INV-2023-01-201',
                'amount' => 250000,
                'period' => 'January 2023',
                'due_date' => '2023-02-15',
                'payment_date' => '2023-02-08',
                'payment_method' => 'bank_transfer',
                'status' => 'completed',
                'customer' => [
                    'id' => '4',
                    'name' => 'Company X',
                ],
            ],
        ];
        
        // Filter payments based on user role
        if ($user['role'] === 'client') {
            $clientId = $user['id'];
            $payments = array_filter($payments, function ($payment) use ($clientId) {
                return $payment['client_id'] === $clientId;
            });
        } else if ($user['role'] === 'customer') {
            $customerId = $user['id'];
            $payments = array_filter($payments, function ($payment) use ($customerId) {
                return $payment['customer_id'] === $customerId;
            });
        }
        
        // Filter payments by client ID if provided in query parameters (for superadmin)
        if ($user['role'] === 'superadmin' && isset($request->getQueryParams()['client_id'])) {
            $clientId = $request->getQueryParams()['client_id'];
            $payments = array_filter($payments, function ($payment) use ($clientId) {
                return $payment['client_id'] === $clientId;
            });
        }
        
        // Filter payments by customer ID if provided in query parameters
        if (isset($request->getQueryParams()['customer_id'])) {
            $customerId = $request->getQueryParams()['customer_id'];
            $payments = array_filter($payments, function ($payment) use ($customerId) {
                return $payment['customer_id'] === $customerId;
            });
        }
        
        // Get query parameters for pagination
        $page = isset($request->getQueryParams()['page']) ? (int) $request->getQueryParams()['page'] : 1;
        $limit = isset($request->getQueryParams()['limit']) ? (int) $request->getQueryParams()['limit'] : 10;
        
        // Get query parameters for filtering
        $status = isset($request->getQueryParams()['status']) ? $request->getQueryParams()['status'] : null;
        $period = isset($request->getQueryParams()['period']) ? $request->getQueryParams()['period'] : null;
        $startDate = isset($request->getQueryParams()['start_date']) ? $request->getQueryParams()['start_date'] : null;
        $endDate = isset($request->getQueryParams()['end_date']) ? $request->getQueryParams()['end_date'] : null;
        
        // Filter payments by status if provided
        if ($status) {
            $payments = array_filter($payments, function ($payment) use ($status) {
                return $payment['status'] === $status;
            });
        }
        
        // Filter payments by period if provided
        if ($period) {
            $payments = array_filter($payments, function ($payment) use ($period) {
                return stripos($payment['period'], $period) !== false;
            });
        }
        
        // Filter payments by start date if provided
        if ($startDate) {
            $payments = array_filter($payments, function ($payment) use ($startDate) {
                return $payment['payment_date'] && strtotime($payment['payment_date']) >= strtotime($startDate);
            });
        }
        
        // Filter payments by end date if provided
        if ($endDate) {
            $payments = array_filter($payments, function ($payment) use ($endDate) {
                return $payment['payment_date'] && strtotime($payment['payment_date']) <= strtotime($endDate);
            });
        }
        
        // Calculate pagination
        $total = count($payments);
        $offset = ($page - 1) * $limit;
        $payments = array_slice($payments, $offset, $limit);
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Payments retrieved successfully',
            'data' => [
                'payments' => array_values($payments), // Reset array keys
                'pagination' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil($total / $limit),
                ],
            ],
        ]);
    }

    public function getPayment(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client, superadmin, or the customer who made the payment
        if ($user['role'] !== 'client' && $user['role'] !== 'superadmin' && $user['role'] !== 'customer') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // Validate input
        if (!isset($args['id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Payment ID is required',
            ], 400);
        }
        
        $paymentId = $args['id'];
        
        // In a real application, you would fetch the payment from the database
        // For demo purposes, we'll return mock data
        $payments = [
            'P-101' => [
                'id' => 'P-101',
                'customer_id' => '1',
                'client_id' => '1',
                'invoice_number' => 'INV-2023-01-101',
                'amount' => 127500,
                'period' => 'January 2023',
                'due_date' => '2023-02-15',
                'payment_date' => '2023-02-05',
                'payment_method' => 'bank_transfer',
                'status' => 'completed',
                'customer' => [
                    'id' => '1',
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+62123456789',
                    'unit' => 'A-101',
                    'property' => [
                        'id' => '1',
                        'name' => 'Apartment Complex A',
                    ],
                ],
                'invoice_details' => [
                    'meter_id' => 'M-101',
                    'previous_reading' => 0,
                    'current_reading' => 25.5,
                    'consumption' => 25.5,
                    'rate' => 5000,
                    'subtotal' => 127500,
                    'tax' => 0,
                    'total' => 127500,
                ],
                'payment_details' => [
                    'transaction_id' => 'TRX-12345',
                    'payment_method' => 'bank_transfer',
                    'bank' => 'Bank ABC',
                    'account_number' => '****6789',
                    'payment_date' => '2023-02-05',
                    'payment_time' => '10:30:00',
                    'status' => 'completed',
                ],
            ],
            'P-102' => [
                'id' => 'P-102',
                'customer_id' => '1',
                'client_id' => '1',
                'invoice_number' => 'INV-2023-02-101',
                'amount' => 126500,
                'period' => 'February 2023',
                'due_date' => '2023-03-15',
                'payment_date' => '2023-03-07',
                'payment_method' => 'bank_transfer',
                'status' => 'completed',
                'customer' => [
                    'id' => '1',
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+62123456789',
                    'unit' => 'A-101',
                    'property' => [
                        'id' => '1',
                        'name' => 'Apartment Complex A',
                    ],
                ],
                'invoice_details' => [
                    'meter_id' => 'M-101',
                    'previous_reading' => 25.5,
                    'current_reading' => 50.8,
                    'consumption' => 25.3,
                    'rate' => 5000,
                    'subtotal' => 126500,
                    'tax' => 0,
                    'total' => 126500,
                ],
                'payment_details' => [
                    'transaction_id' => 'TRX-23456',
                    'payment_method' => 'bank_transfer',
                    'bank' => 'Bank ABC',
                    'account_number' => '****6789',
                    'payment_date' => '2023-03-07',
                    'payment_time' => '11:45:00',
                    'status' => 'completed',
                ],
            ],
            'P-103' => [
                'id' => 'P-103',
                'customer_id' => '1',
                'client_id' => '1',
                'invoice_number' => 'INV-2023-03-101',
                'amount' => 122000,
                'period' => 'March 2023',
                'due_date' => '2023-04-15',
                'payment_date' => '2023-04-05',
                'payment_method' => 'e_wallet',
                'status' => 'completed',
                'customer' => [
                    'id' => '1',
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+62123456789',
                    'unit' => 'A-101',
                    'property' => [
                        'id' => '1',
                        'name' => 'Apartment Complex A',
                    ],
                ],
                'invoice_details' => [
                    'meter_id' => 'M-101',
                    'previous_reading' => 50.8,
                    'current_reading' => 75.2,
                    'consumption' => 24.4,
                    'rate' => 5000,
                    'subtotal' => 122000,
                    'tax' => 0,
                    'total' => 122000,
                ],
                'payment_details' => [
                    'transaction_id' => 'TRX-34567',
                    'payment_method' => 'e_wallet',
                    'wallet_provider' => 'GoPay',
                    'wallet_account' => 'john.doe@example.com',
                    'payment_date' => '2023-04-05',
                    'payment_time' => '09:15:00',
                    'status' => 'completed',
                ],
            ],
            'P-104' => [
                'id' => 'P-104',
                'customer_id' => '2',
                'client_id' => '1',
                'invoice_number' => 'INV-2023-01-102',
                'amount' => 130000,
                'period' => 'January 2023',
                'due_date' => '2023-02-15',
                'payment_date' => '2023-02-10',
                'payment_method' => 'credit_card',
                'status' => 'completed',
                'customer' => [
                    'id' => '2',
                    'name' => 'Jane Smith',
                    'email' => 'jane.smith@example.com',
                    'phone' => '+62987654321',
                    'unit' => 'A-102',
                    'property' => [
                        'id' => '1',
                        'name' => 'Apartment Complex A',
                    ],
                ],
                'invoice_details' => [
                    'meter_id' => 'M-102',
                    'previous_reading' => 0,
                    'current_reading' => 26,
                    'consumption' => 26,
                    'rate' => 5000,
                    'subtotal' => 130000,
                    'tax' => 0,
                    'total' => 130000,
                ],
                'payment_details' => [
                    'transaction_id' => 'TRX-45678',
                    'payment_method' => 'credit_card',
                    'card_type' => 'Visa',
                    'card_number' => '****5678',
                    'payment_date' => '2023-02-10',
                    'payment_time' => '14:20:00',
                    'status' => 'completed',
                ],
            ],
            'P-105' => [
                'id' => 'P-105',
                'customer_id' => '3',
                'client_id' => '1',
                'invoice_number' => 'INV-2023-01-103',
                'amount' => 115000,
                'period' => 'January 2023',
                'due_date' => '2023-02-15',
                'payment_date' => null,
                'payment_method' => null,
                'status' => 'pending',
                'customer' => [
                    'id' => '3',
                    'name' => 'Bob Johnson',
                    'email' => 'bob.johnson@example.com',
                    'phone' => '+62456789123',
                    'unit' => 'B-101',
                    'property' => [
                        'id' => '2',
                        'name' => 'Residential Area B',
                    ],
                ],
                'invoice_details' => [
                    'meter_id' => 'M-201',
                    'previous_reading' => 0,
                    'current_reading' => 23,
                    'consumption' => 23,
                    'rate' => 5000,
                    'subtotal' => 115000,
                    'tax' => 0,
                    'total' => 115000,
                ],
                'payment_details' => null,
            ],
        ];
        
        if (!isset($payments[$paymentId])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }
        
        $payment = $payments[$paymentId];
        
        // Check if the client user has access to this payment
        if ($user['role'] === 'client' && $payment['client_id'] !== $user['id']) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access to this payment',
            ], 403);
        }
        
        // Check if the customer user has access to this payment
        if ($user['role'] === 'customer' && $payment['customer_id'] !== $user['id']) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access to this payment',
            ], 403);
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Payment retrieved successfully',
            'data' => $payment,
        ]);
    }

    public function createPayment(Request $request, Response $response): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client or superadmin
        if ($user['role'] !== 'client' && $user['role'] !== 'superadmin') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['customer_id']) || !isset($data['amount']) || !isset($data['period']) || !isset($data['due_date'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Customer ID, amount, period, and due date are required',
            ], 400);
        }
        
        // If user is a client, use their client ID
        // If user is a superadmin, client_id must be provided
        $clientId = null;
        if ($user['role'] === 'client') {
            $clientId = $user['id'];
        } else if ($user['role'] === 'superadmin') {
            if (!isset($data['client_id'])) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Client ID is required for superadmin',
                ], 400);
            }
            $clientId = $data['client_id'];
        }
        
        // In a real application, you would save the payment to the database
        // For demo purposes, we'll just return a success response with mock data
        $payment = [
            'id' => 'P-' . rand(1000, 9999), // In a real application, this would be generated by the database
            'customer_id' => $data['customer_id'],
            'client_id' => $clientId,
            'invoice_number' => 'INV-' . date('Y-m-') . rand(100, 999),
            'amount' => (float) $data['amount'],
            'period' => $data['period'],
            'due_date' => $data['due_date'],
            'payment_date' => null,
            'payment_method' => null,
            'status' => 'pending',
            'customer' => [
                'id' => $data['customer_id'],
                'name' => 'Customer Name', // In a real application, this would be fetched from the database
            ],
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Payment created successfully',
            'data' => $payment,
        ], 201);
    }

    public function updatePayment(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client or superadmin
        if ($user['role'] !== 'client' && $user['role'] !== 'superadmin') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // Validate input
        if (!isset($args['id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Payment ID is required',
            ], 400);
        }
        
        $paymentId = $args['id'];
        $data = $request->getParsedBody();
        
        // In a real application, you would fetch the payment from the database
        // and check if the client user has access to it
        
        // For demo purposes, we'll just return a success response with mock data
        $payment = [
            'id' => $paymentId,
            'customer_id' => $data['customer_id'] ?? '1',
            'client_id' => $user['role'] === 'client' ? $user['id'] : ($data['client_id'] ?? '1'),
            'invoice_number' => $data['invoice_number'] ?? 'INV-2023-01-101',
            'amount' => $data['amount'] ?? 127500,
            'period' => $data['period'] ?? 'January 2023',
            'due_date' => $data['due_date'] ?? '2023-02-15',
            'payment_date' => $data['payment_date'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'customer' => [
                'id' => $data['customer_id'] ?? '1',
                'name' => 'Customer Name', // In a real application, this would be fetched from the database
            ],
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Payment updated successfully',
            'data' => $payment,
        ]);
    }

    public function deletePayment(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client or superadmin
        if ($user['role'] !== 'client' && $user['role'] !== 'superadmin') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // Validate input
        if (!isset($args['id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Payment ID is required',
            ], 400);
        }
        
        $paymentId = $args['id'];
        
        // In a real application, you would fetch the payment from the database
        // and check if the client user has access to it before deleting
        
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Payment deleted successfully',
        ]);
    }

    public function processPayment(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a customer (only customers can make payments)
        if ($user['role'] !== 'customer') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // Validate input
        if (!isset($args['id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Payment ID is required',
            ], 400);
        }
        
        $paymentId = $args['id'];
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['payment_method'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Payment method is required',
            ], 400);
        }
        
        // In a real application, you would fetch the payment from the database
        // and check if the customer user has access to it
        
        // For demo purposes, we'll just return a success response with mock data
        $payment = [
            'id' => $paymentId,
            'customer_id' => $user['id'],
            'client_id' => '1', // In a real application, this would be fetched from the database
            'invoice_number' => 'INV-2023-01-101',
            'amount' => 127500,
            'period' => 'January 2023',
            'due_date' => '2023-02-15',
            'payment_date' => date('Y-m-d'),
            'payment_method' => $data['payment_method'],
            'status' => 'completed',
            'customer' => [
                'id' => $user['id'],
                'name' => $user['name'],
            ],
            'payment_details' => [
                'transaction_id' => 'TRX-' . rand(10000, 99999),
                'payment_method' => $data['payment_method'],
                'payment_date' => date('Y-m-d'),
                'payment_time' => date('H:i:s'),
                'status' => 'completed',
            ],
        ];
        
        // Add payment method specific details
        if ($data['payment_method'] === 'bank_transfer') {
            $payment['payment_details']['bank'] = $data['bank'] ?? 'Bank ABC';
            $payment['payment_details']['account_number'] = $data['account_number'] ?? '****6789';
        } else if ($data['payment_method'] === 'credit_card') {
            $payment['payment_details']['card_type'] = $data['card_type'] ?? 'Visa';
            $payment['payment_details']['card_number'] = $data['card_number'] ?? '****5678';
        } else if ($data['payment_method'] === 'e_wallet') {
            $payment['payment_details']['wallet_provider'] = $data['wallet_provider'] ?? 'GoPay';
            $payment['payment_details']['wallet_account'] = $data['wallet_account'] ?? $user['email'];
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => $payment,
        ]);
    }

    public function generateInvoice(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client, superadmin, or the customer who owns the invoice
        if ($user['role'] !== 'client' && $user['role'] !== 'superadmin' && $user['role'] !== 'customer') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // Validate input
        if (!isset($args['id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Payment ID is required',
            ], 400);
        }
        
        $paymentId = $args['id'];
        
        // In a real application, you would fetch the payment from the database
        // and check if the user has access to it
        
        // For demo purposes, we'll just return a success response with mock data
        $invoice = [
            'id' => $paymentId,
            'invoice_number' => 'INV-2023-01-101',
            'date' => '2023-01-31',
            'due_date' => '2023-02-15',
            'customer' => [
                'id' => '1',
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+62123456789',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'unit' => 'A-101',
            ],
            'client' => [
                'id' => '1',
                'name' => 'ABC Water Company',
                'email' => 'contact@abcwater.com',
                'phone' => '+62123456789',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
            ],
            'property' => [
                'id' => '1',
                'name' => 'Apartment Complex A',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
            ],
            'meter' => [
                'id' => 'M-101',
                'serial_number' => 'SN12345',
            ],
            'period' => 'January 2023',
            'previous_reading' => 0,
            'current_reading' => 25.5,
            'consumption' => 25.5,
            'rate' => 5000,
            'subtotal' => 127500,
            'tax' => 0,
            'total' => 127500,
            'status' => 'paid',
            'payment_date' => '2023-02-05',
            'payment_method' => 'bank_transfer',
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Invoice generated successfully',
            'data' => $invoice,
        ]);
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}