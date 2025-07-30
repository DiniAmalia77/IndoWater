<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CustomerController
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getCustomers(Request $request, Response $response): Response
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
        
        // In a real application, you would fetch customers from the database
        // For client users, you would only fetch customers belonging to their client account
        // For superadmin users, you would fetch all customers or filter by client ID if provided
        
        // For demo purposes, we'll return mock data
        $customers = [
            [
                'id' => '1',
                'client_id' => '1',
                'property_id' => '1',
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+62123456789',
                'unit' => 'A-101',
                'meter_id' => 'M-101',
                'status' => 'active',
                'created_at' => '2023-01-25T10:30:00Z',
                'property' => [
                    'id' => '1',
                    'name' => 'Apartment Complex A',
                ],
            ],
            [
                'id' => '2',
                'client_id' => '1',
                'property_id' => '1',
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+62987654321',
                'unit' => 'A-102',
                'meter_id' => 'M-102',
                'status' => 'active',
                'created_at' => '2023-01-26T11:45:00Z',
                'property' => [
                    'id' => '1',
                    'name' => 'Apartment Complex A',
                ],
            ],
            [
                'id' => '3',
                'client_id' => '1',
                'property_id' => '2',
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@example.com',
                'phone' => '+62456789123',
                'unit' => 'B-101',
                'meter_id' => 'M-201',
                'status' => 'active',
                'created_at' => '2023-02-10T09:15:00Z',
                'property' => [
                    'id' => '2',
                    'name' => 'Residential Area B',
                ],
            ],
            [
                'id' => '4',
                'client_id' => '2',
                'property_id' => '3',
                'name' => 'Company X',
                'email' => 'info@companyx.com',
                'phone' => '+62789123456',
                'unit' => 'C-101',
                'meter_id' => 'M-301',
                'status' => 'active',
                'created_at' => '2023-03-15T14:30:00Z',
                'property' => [
                    'id' => '3',
                    'name' => 'Commercial Building C',
                ],
            ],
        ];
        
        // Filter customers by client ID if the user is a client
        if ($user['role'] === 'client') {
            $clientId = $user['id'];
            $customers = array_filter($customers, function ($customer) use ($clientId) {
                return $customer['client_id'] === $clientId;
            });
        }
        
        // Filter customers by client ID if provided in query parameters (for superadmin)
        if ($user['role'] === 'superadmin' && isset($request->getQueryParams()['client_id'])) {
            $clientId = $request->getQueryParams()['client_id'];
            $customers = array_filter($customers, function ($customer) use ($clientId) {
                return $customer['client_id'] === $clientId;
            });
        }
        
        // Filter customers by property ID if provided in query parameters
        if (isset($request->getQueryParams()['property_id'])) {
            $propertyId = $request->getQueryParams()['property_id'];
            $customers = array_filter($customers, function ($customer) use ($propertyId) {
                return $customer['property_id'] === $propertyId;
            });
        }
        
        // Get query parameters for pagination
        $page = isset($request->getQueryParams()['page']) ? (int) $request->getQueryParams()['page'] : 1;
        $limit = isset($request->getQueryParams()['limit']) ? (int) $request->getQueryParams()['limit'] : 10;
        
        // Get query parameters for filtering
        $status = isset($request->getQueryParams()['status']) ? $request->getQueryParams()['status'] : null;
        $search = isset($request->getQueryParams()['search']) ? $request->getQueryParams()['search'] : null;
        
        // Filter customers by status if provided
        if ($status) {
            $customers = array_filter($customers, function ($customer) use ($status) {
                return $customer['status'] === $status;
            });
        }
        
        // Filter customers by search term if provided
        if ($search) {
            $customers = array_filter($customers, function ($customer) use ($search) {
                return stripos($customer['name'], $search) !== false || 
                       stripos($customer['email'], $search) !== false || 
                       stripos($customer['phone'], $search) !== false || 
                       stripos($customer['unit'], $search) !== false || 
                       stripos($customer['meter_id'], $search) !== false;
            });
        }
        
        // Calculate pagination
        $total = count($customers);
        $offset = ($page - 1) * $limit;
        $customers = array_slice($customers, $offset, $limit);
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => [
                'customers' => array_values($customers), // Reset array keys
                'pagination' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil($total / $limit),
                ],
            ],
        ]);
    }

    public function getCustomer(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client, superadmin, or the customer themselves
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
                'message' => 'Customer ID is required',
            ], 400);
        }
        
        $customerId = $args['id'];
        
        // In a real application, you would fetch the customer from the database
        // For demo purposes, we'll return mock data
        $customers = [
            '1' => [
                'id' => '1',
                'client_id' => '1',
                'property_id' => '1',
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+62123456789',
                'unit' => 'A-101',
                'meter_id' => 'M-101',
                'status' => 'active',
                'created_at' => '2023-01-25T10:30:00Z',
                'property' => [
                    'id' => '1',
                    'name' => 'Apartment Complex A',
                    'address' => 'Jl. Sudirman No. 123, Jakarta',
                ],
                'client' => [
                    'id' => '1',
                    'name' => 'ABC Water Company',
                ],
                'meter' => [
                    'id' => 'M-101',
                    'serial_number' => 'SN12345',
                    'installation_date' => '2023-01-25',
                    'last_reading_date' => '2023-06-25',
                    'last_reading_value' => 150.5,
                    'status' => 'active',
                ],
                'consumption_history' => [
                    [
                        'month' => 'January',
                        'year' => 2023,
                        'reading_date' => '2023-01-31',
                        'reading_value' => 25.5,
                        'consumption' => 25.5,
                        'amount' => 127500,
                        'status' => 'paid',
                    ],
                    [
                        'month' => 'February',
                        'year' => 2023,
                        'reading_date' => '2023-02-28',
                        'reading_value' => 50.8,
                        'consumption' => 25.3,
                        'amount' => 126500,
                        'status' => 'paid',
                    ],
                    [
                        'month' => 'March',
                        'year' => 2023,
                        'reading_date' => '2023-03-31',
                        'reading_value' => 75.2,
                        'consumption' => 24.4,
                        'amount' => 122000,
                        'status' => 'paid',
                    ],
                    [
                        'month' => 'April',
                        'year' => 2023,
                        'reading_date' => '2023-04-30',
                        'reading_value' => 100.6,
                        'consumption' => 25.4,
                        'amount' => 127000,
                        'status' => 'paid',
                    ],
                    [
                        'month' => 'May',
                        'year' => 2023,
                        'reading_date' => '2023-05-31',
                        'reading_value' => 125.3,
                        'consumption' => 24.7,
                        'amount' => 123500,
                        'status' => 'paid',
                    ],
                    [
                        'month' => 'June',
                        'year' => 2023,
                        'reading_date' => '2023-06-25',
                        'reading_value' => 150.5,
                        'consumption' => 25.2,
                        'amount' => 126000,
                        'status' => 'unpaid',
                    ],
                ],
                'payment_history' => [
                    [
                        'id' => 'P-101',
                        'date' => '2023-02-05',
                        'amount' => 127500,
                        'method' => 'bank_transfer',
                        'status' => 'completed',
                        'invoice_number' => 'INV-2023-01-101',
                        'period' => 'January 2023',
                    ],
                    [
                        'id' => 'P-102',
                        'date' => '2023-03-07',
                        'amount' => 126500,
                        'method' => 'bank_transfer',
                        'status' => 'completed',
                        'invoice_number' => 'INV-2023-02-101',
                        'period' => 'February 2023',
                    ],
                    [
                        'id' => 'P-103',
                        'date' => '2023-04-05',
                        'amount' => 122000,
                        'method' => 'e_wallet',
                        'status' => 'completed',
                        'invoice_number' => 'INV-2023-03-101',
                        'period' => 'March 2023',
                    ],
                    [
                        'id' => 'P-104',
                        'date' => '2023-05-06',
                        'amount' => 127000,
                        'method' => 'e_wallet',
                        'status' => 'completed',
                        'invoice_number' => 'INV-2023-04-101',
                        'period' => 'April 2023',
                    ],
                    [
                        'id' => 'P-105',
                        'date' => '2023-06-08',
                        'amount' => 123500,
                        'method' => 'credit_card',
                        'status' => 'completed',
                        'invoice_number' => 'INV-2023-05-101',
                        'period' => 'May 2023',
                    ],
                ],
            ],
            '2' => [
                'id' => '2',
                'client_id' => '1',
                'property_id' => '1',
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+62987654321',
                'unit' => 'A-102',
                'meter_id' => 'M-102',
                'status' => 'active',
                'created_at' => '2023-01-26T11:45:00Z',
                'property' => [
                    'id' => '1',
                    'name' => 'Apartment Complex A',
                    'address' => 'Jl. Sudirman No. 123, Jakarta',
                ],
                'client' => [
                    'id' => '1',
                    'name' => 'ABC Water Company',
                ],
                'meter' => [
                    'id' => 'M-102',
                    'serial_number' => 'SN12346',
                    'installation_date' => '2023-01-26',
                    'last_reading_date' => '2023-06-26',
                    'last_reading_value' => 145.8,
                    'status' => 'active',
                ],
                // Similar consumption and payment history would be here
            ],
            '3' => [
                'id' => '3',
                'client_id' => '1',
                'property_id' => '2',
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@example.com',
                'phone' => '+62456789123',
                'unit' => 'B-101',
                'meter_id' => 'M-201',
                'status' => 'active',
                'created_at' => '2023-02-10T09:15:00Z',
                'property' => [
                    'id' => '2',
                    'name' => 'Residential Area B',
                    'address' => 'Jl. Thamrin No. 456, Jakarta',
                ],
                'client' => [
                    'id' => '1',
                    'name' => 'ABC Water Company',
                ],
                'meter' => [
                    'id' => 'M-201',
                    'serial_number' => 'SN23456',
                    'installation_date' => '2023-02-10',
                    'last_reading_date' => '2023-06-20',
                    'last_reading_value' => 130.2,
                    'status' => 'active',
                ],
                // Similar consumption and payment history would be here
            ],
            '4' => [
                'id' => '4',
                'client_id' => '2',
                'property_id' => '3',
                'name' => 'Company X',
                'email' => 'info@companyx.com',
                'phone' => '+62789123456',
                'unit' => 'C-101',
                'meter_id' => 'M-301',
                'status' => 'active',
                'created_at' => '2023-03-15T14:30:00Z',
                'property' => [
                    'id' => '3',
                    'name' => 'Commercial Building C',
                    'address' => 'Jl. Gatot Subroto No. 789, Jakarta',
                ],
                'client' => [
                    'id' => '2',
                    'name' => 'XYZ Utilities',
                ],
                'meter' => [
                    'id' => 'M-301',
                    'serial_number' => 'SN34567',
                    'installation_date' => '2023-03-15',
                    'last_reading_date' => '2023-06-15',
                    'last_reading_value' => 280.5,
                    'status' => 'active',
                ],
                // Similar consumption and payment history would be here
            ],
        ];
        
        if (!isset($customers[$customerId])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }
        
        $customer = $customers[$customerId];
        
        // Check if the client user has access to this customer
        if ($user['role'] === 'client' && $customer['client_id'] !== $user['id']) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access to this customer',
            ], 403);
        }
        
        // Check if the customer user is accessing their own data
        if ($user['role'] === 'customer' && $user['id'] !== $customerId) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access to this customer',
            ], 403);
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Customer retrieved successfully',
            'data' => $customer,
        ]);
    }

    public function createCustomer(Request $request, Response $response): Response
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
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['phone']) || 
            !isset($data['property_id']) || !isset($data['unit']) || !isset($data['meter_id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Name, email, phone, property ID, unit, and meter ID are required',
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
        
        // In a real application, you would save the customer to the database
        // For demo purposes, we'll just return a success response with mock data
        $customer = [
            'id' => '5', // In a real application, this would be generated by the database
            'client_id' => $clientId,
            'property_id' => $data['property_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'unit' => $data['unit'],
            'meter_id' => $data['meter_id'],
            'status' => 'active',
            'created_at' => date('Y-m-d\TH:i:s\Z'),
            'property' => [
                'id' => $data['property_id'],
                'name' => 'Property Name', // In a real application, this would be fetched from the database
            ],
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer,
        ], 201);
    }

    public function updateCustomer(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client, superadmin, or the customer themselves
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
                'message' => 'Customer ID is required',
            ], 400);
        }
        
        $customerId = $args['id'];
        $data = $request->getParsedBody();
        
        // In a real application, you would fetch the customer from the database
        // and check if the client user has access to it
        
        // For demo purposes, we'll just return a success response with mock data
        $customer = [
            'id' => $customerId,
            'client_id' => $user['role'] === 'client' ? $user['id'] : ($data['client_id'] ?? '1'),
            'property_id' => $data['property_id'] ?? '1',
            'name' => $data['name'] ?? 'Updated Customer',
            'email' => $data['email'] ?? 'updated@example.com',
            'phone' => $data['phone'] ?? '+62123456789',
            'unit' => $data['unit'] ?? 'A-101',
            'meter_id' => $data['meter_id'] ?? 'M-101',
            'status' => $data['status'] ?? 'active',
            'created_at' => '2023-01-25T10:30:00Z',
            'property' => [
                'id' => $data['property_id'] ?? '1',
                'name' => 'Property Name', // In a real application, this would be fetched from the database
            ],
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer,
        ]);
    }

    public function deleteCustomer(Request $request, Response $response, array $args): Response
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
                'message' => 'Customer ID is required',
            ], 400);
        }
        
        $customerId = $args['id'];
        
        // In a real application, you would fetch the customer from the database
        // and check if the client user has access to it before deleting
        
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Customer deleted successfully',
        ]);
    }

    public function getCustomerConsumption(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client, superadmin, or the customer themselves
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
                'message' => 'Customer ID is required',
            ], 400);
        }
        
        $customerId = $args['id'];
        
        // In a real application, you would fetch the customer consumption from the database
        // and check if the client user or customer has access to it
        
        // For demo purposes, we'll return mock data
        $consumption = [
            [
                'month' => 'January',
                'year' => 2023,
                'reading_date' => '2023-01-31',
                'reading_value' => 25.5,
                'consumption' => 25.5,
                'amount' => 127500,
                'status' => 'paid',
            ],
            [
                'month' => 'February',
                'year' => 2023,
                'reading_date' => '2023-02-28',
                'reading_value' => 50.8,
                'consumption' => 25.3,
                'amount' => 126500,
                'status' => 'paid',
            ],
            [
                'month' => 'March',
                'year' => 2023,
                'reading_date' => '2023-03-31',
                'reading_value' => 75.2,
                'consumption' => 24.4,
                'amount' => 122000,
                'status' => 'paid',
            ],
            [
                'month' => 'April',
                'year' => 2023,
                'reading_date' => '2023-04-30',
                'reading_value' => 100.6,
                'consumption' => 25.4,
                'amount' => 127000,
                'status' => 'paid',
            ],
            [
                'month' => 'May',
                'year' => 2023,
                'reading_date' => '2023-05-31',
                'reading_value' => 125.3,
                'consumption' => 24.7,
                'amount' => 123500,
                'status' => 'paid',
            ],
            [
                'month' => 'June',
                'year' => 2023,
                'reading_date' => '2023-06-25',
                'reading_value' => 150.5,
                'consumption' => 25.2,
                'amount' => 126000,
                'status' => 'unpaid',
            ],
        ];
        
        // Get query parameters for filtering
        $year = isset($request->getQueryParams()['year']) ? (int) $request->getQueryParams()['year'] : null;
        $month = isset($request->getQueryParams()['month']) ? $request->getQueryParams()['month'] : null;
        $status = isset($request->getQueryParams()['status']) ? $request->getQueryParams()['status'] : null;
        
        // Filter consumption by year if provided
        if ($year) {
            $consumption = array_filter($consumption, function ($item) use ($year) {
                return $item['year'] === $year;
            });
        }
        
        // Filter consumption by month if provided
        if ($month) {
            $consumption = array_filter($consumption, function ($item) use ($month) {
                return strtolower($item['month']) === strtolower($month);
            });
        }
        
        // Filter consumption by status if provided
        if ($status) {
            $consumption = array_filter($consumption, function ($item) use ($status) {
                return $item['status'] === $status;
            });
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Customer consumption retrieved successfully',
            'data' => array_values($consumption), // Reset array keys
        ]);
    }

    public function getCustomerPayments(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client, superadmin, or the customer themselves
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
                'message' => 'Customer ID is required',
            ], 400);
        }
        
        $customerId = $args['id'];
        
        // In a real application, you would fetch the customer payments from the database
        // and check if the client user or customer has access to it
        
        // For demo purposes, we'll return mock data
        $payments = [
            [
                'id' => 'P-101',
                'date' => '2023-02-05',
                'amount' => 127500,
                'method' => 'bank_transfer',
                'status' => 'completed',
                'invoice_number' => 'INV-2023-01-101',
                'period' => 'January 2023',
            ],
            [
                'id' => 'P-102',
                'date' => '2023-03-07',
                'amount' => 126500,
                'method' => 'bank_transfer',
                'status' => 'completed',
                'invoice_number' => 'INV-2023-02-101',
                'period' => 'February 2023',
            ],
            [
                'id' => 'P-103',
                'date' => '2023-04-05',
                'amount' => 122000,
                'method' => 'e_wallet',
                'status' => 'completed',
                'invoice_number' => 'INV-2023-03-101',
                'period' => 'March 2023',
            ],
            [
                'id' => 'P-104',
                'date' => '2023-05-06',
                'amount' => 127000,
                'method' => 'e_wallet',
                'status' => 'completed',
                'invoice_number' => 'INV-2023-04-101',
                'period' => 'April 2023',
            ],
            [
                'id' => 'P-105',
                'date' => '2023-06-08',
                'amount' => 123500,
                'method' => 'credit_card',
                'status' => 'completed',
                'invoice_number' => 'INV-2023-05-101',
                'period' => 'May 2023',
            ],
        ];
        
        // Get query parameters for filtering
        $status = isset($request->getQueryParams()['status']) ? $request->getQueryParams()['status'] : null;
        $method = isset($request->getQueryParams()['method']) ? $request->getQueryParams()['method'] : null;
        $period = isset($request->getQueryParams()['period']) ? $request->getQueryParams()['period'] : null;
        
        // Filter payments by status if provided
        if ($status) {
            $payments = array_filter($payments, function ($payment) use ($status) {
                return $payment['status'] === $status;
            });
        }
        
        // Filter payments by method if provided
        if ($method) {
            $payments = array_filter($payments, function ($payment) use ($method) {
                return $payment['method'] === $method;
            });
        }
        
        // Filter payments by period if provided
        if ($period) {
            $payments = array_filter($payments, function ($payment) use ($period) {
                return stripos($payment['period'], $period) !== false;
            });
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Customer payments retrieved successfully',
            'data' => array_values($payments), // Reset array keys
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