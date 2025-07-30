<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MeterController
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getMeters(Request $request, Response $response): Response
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
        
        // In a real application, you would fetch meters from the database
        // For client users, you would only fetch meters belonging to their client account
        // For superadmin users, you would fetch all meters or filter by client ID if provided
        
        // For demo purposes, we'll return mock data
        $meters = [
            [
                'id' => 'M-101',
                'client_id' => '1',
                'property_id' => '1',
                'customer_id' => '1',
                'serial_number' => 'SN12345',
                'installation_date' => '2023-01-25',
                'last_reading_date' => '2023-06-25',
                'last_reading_value' => 150.5,
                'status' => 'active',
                'customer' => [
                    'id' => '1',
                    'name' => 'John Doe',
                    'unit' => 'A-101',
                ],
                'property' => [
                    'id' => '1',
                    'name' => 'Apartment Complex A',
                ],
            ],
            [
                'id' => 'M-102',
                'client_id' => '1',
                'property_id' => '1',
                'customer_id' => '2',
                'serial_number' => 'SN12346',
                'installation_date' => '2023-01-26',
                'last_reading_date' => '2023-06-26',
                'last_reading_value' => 145.8,
                'status' => 'active',
                'customer' => [
                    'id' => '2',
                    'name' => 'Jane Smith',
                    'unit' => 'A-102',
                ],
                'property' => [
                    'id' => '1',
                    'name' => 'Apartment Complex A',
                ],
            ],
            [
                'id' => 'M-201',
                'client_id' => '1',
                'property_id' => '2',
                'customer_id' => '3',
                'serial_number' => 'SN23456',
                'installation_date' => '2023-02-10',
                'last_reading_date' => '2023-06-20',
                'last_reading_value' => 130.2,
                'status' => 'active',
                'customer' => [
                    'id' => '3',
                    'name' => 'Bob Johnson',
                    'unit' => 'B-101',
                ],
                'property' => [
                    'id' => '2',
                    'name' => 'Residential Area B',
                ],
            ],
            [
                'id' => 'M-301',
                'client_id' => '2',
                'property_id' => '3',
                'customer_id' => '4',
                'serial_number' => 'SN34567',
                'installation_date' => '2023-03-15',
                'last_reading_date' => '2023-06-15',
                'last_reading_value' => 280.5,
                'status' => 'active',
                'customer' => [
                    'id' => '4',
                    'name' => 'Company X',
                    'unit' => 'C-101',
                ],
                'property' => [
                    'id' => '3',
                    'name' => 'Commercial Building C',
                ],
            ],
        ];
        
        // Filter meters by client ID if the user is a client
        if ($user['role'] === 'client') {
            $clientId = $user['id'];
            $meters = array_filter($meters, function ($meter) use ($clientId) {
                return $meter['client_id'] === $clientId;
            });
        }
        
        // Filter meters by client ID if provided in query parameters (for superadmin)
        if ($user['role'] === 'superadmin' && isset($request->getQueryParams()['client_id'])) {
            $clientId = $request->getQueryParams()['client_id'];
            $meters = array_filter($meters, function ($meter) use ($clientId) {
                return $meter['client_id'] === $clientId;
            });
        }
        
        // Filter meters by property ID if provided in query parameters
        if (isset($request->getQueryParams()['property_id'])) {
            $propertyId = $request->getQueryParams()['property_id'];
            $meters = array_filter($meters, function ($meter) use ($propertyId) {
                return $meter['property_id'] === $propertyId;
            });
        }
        
        // Filter meters by customer ID if provided in query parameters
        if (isset($request->getQueryParams()['customer_id'])) {
            $customerId = $request->getQueryParams()['customer_id'];
            $meters = array_filter($meters, function ($meter) use ($customerId) {
                return $meter['customer_id'] === $customerId;
            });
        }
        
        // Get query parameters for pagination
        $page = isset($request->getQueryParams()['page']) ? (int) $request->getQueryParams()['page'] : 1;
        $limit = isset($request->getQueryParams()['limit']) ? (int) $request->getQueryParams()['limit'] : 10;
        
        // Get query parameters for filtering
        $status = isset($request->getQueryParams()['status']) ? $request->getQueryParams()['status'] : null;
        $search = isset($request->getQueryParams()['search']) ? $request->getQueryParams()['search'] : null;
        
        // Filter meters by status if provided
        if ($status) {
            $meters = array_filter($meters, function ($meter) use ($status) {
                return $meter['status'] === $status;
            });
        }
        
        // Filter meters by search term if provided
        if ($search) {
            $meters = array_filter($meters, function ($meter) use ($search) {
                return stripos($meter['id'], $search) !== false || 
                       stripos($meter['serial_number'], $search) !== false || 
                       stripos($meter['customer']['name'], $search) !== false || 
                       stripos($meter['customer']['unit'], $search) !== false;
            });
        }
        
        // Calculate pagination
        $total = count($meters);
        $offset = ($page - 1) * $limit;
        $meters = array_slice($meters, $offset, $limit);
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Meters retrieved successfully',
            'data' => [
                'meters' => array_values($meters), // Reset array keys
                'pagination' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil($total / $limit),
                ],
            ],
        ]);
    }

    public function getMeter(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client, superadmin, or the customer who owns the meter
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
                'message' => 'Meter ID is required',
            ], 400);
        }
        
        $meterId = $args['id'];
        
        // In a real application, you would fetch the meter from the database
        // For demo purposes, we'll return mock data
        $meters = [
            'M-101' => [
                'id' => 'M-101',
                'client_id' => '1',
                'property_id' => '1',
                'customer_id' => '1',
                'serial_number' => 'SN12345',
                'installation_date' => '2023-01-25',
                'last_reading_date' => '2023-06-25',
                'last_reading_value' => 150.5,
                'status' => 'active',
                'customer' => [
                    'id' => '1',
                    'name' => 'John Doe',
                    'unit' => 'A-101',
                ],
                'property' => [
                    'id' => '1',
                    'name' => 'Apartment Complex A',
                    'address' => 'Jl. Sudirman No. 123, Jakarta',
                ],
                'client' => [
                    'id' => '1',
                    'name' => 'ABC Water Company',
                ],
                'readings' => [
                    [
                        'date' => '2023-01-31',
                        'value' => 25.5,
                        'consumption' => 25.5,
                        'status' => 'verified',
                    ],
                    [
                        'date' => '2023-02-28',
                        'value' => 50.8,
                        'consumption' => 25.3,
                        'status' => 'verified',
                    ],
                    [
                        'date' => '2023-03-31',
                        'value' => 75.2,
                        'consumption' => 24.4,
                        'status' => 'verified',
                    ],
                    [
                        'date' => '2023-04-30',
                        'value' => 100.6,
                        'consumption' => 25.4,
                        'status' => 'verified',
                    ],
                    [
                        'date' => '2023-05-31',
                        'value' => 125.3,
                        'consumption' => 24.7,
                        'status' => 'verified',
                    ],
                    [
                        'date' => '2023-06-25',
                        'value' => 150.5,
                        'consumption' => 25.2,
                        'status' => 'verified',
                    ],
                ],
                'maintenance_history' => [
                    [
                        'date' => '2023-01-25',
                        'type' => 'installation',
                        'description' => 'Initial installation',
                        'technician' => 'Tech A',
                    ],
                    [
                        'date' => '2023-03-15',
                        'type' => 'inspection',
                        'description' => 'Routine inspection',
                        'technician' => 'Tech B',
                    ],
                    [
                        'date' => '2023-05-20',
                        'type' => 'calibration',
                        'description' => 'Meter calibration',
                        'technician' => 'Tech C',
                    ],
                ],
            ],
            'M-102' => [
                'id' => 'M-102',
                'client_id' => '1',
                'property_id' => '1',
                'customer_id' => '2',
                'serial_number' => 'SN12346',
                'installation_date' => '2023-01-26',
                'last_reading_date' => '2023-06-26',
                'last_reading_value' => 145.8,
                'status' => 'active',
                'customer' => [
                    'id' => '2',
                    'name' => 'Jane Smith',
                    'unit' => 'A-102',
                ],
                'property' => [
                    'id' => '1',
                    'name' => 'Apartment Complex A',
                    'address' => 'Jl. Sudirman No. 123, Jakarta',
                ],
                'client' => [
                    'id' => '1',
                    'name' => 'ABC Water Company',
                ],
                // Similar readings and maintenance history would be here
            ],
            'M-201' => [
                'id' => 'M-201',
                'client_id' => '1',
                'property_id' => '2',
                'customer_id' => '3',
                'serial_number' => 'SN23456',
                'installation_date' => '2023-02-10',
                'last_reading_date' => '2023-06-20',
                'last_reading_value' => 130.2,
                'status' => 'active',
                'customer' => [
                    'id' => '3',
                    'name' => 'Bob Johnson',
                    'unit' => 'B-101',
                ],
                'property' => [
                    'id' => '2',
                    'name' => 'Residential Area B',
                    'address' => 'Jl. Thamrin No. 456, Jakarta',
                ],
                'client' => [
                    'id' => '1',
                    'name' => 'ABC Water Company',
                ],
                // Similar readings and maintenance history would be here
            ],
            'M-301' => [
                'id' => 'M-301',
                'client_id' => '2',
                'property_id' => '3',
                'customer_id' => '4',
                'serial_number' => 'SN34567',
                'installation_date' => '2023-03-15',
                'last_reading_date' => '2023-06-15',
                'last_reading_value' => 280.5,
                'status' => 'active',
                'customer' => [
                    'id' => '4',
                    'name' => 'Company X',
                    'unit' => 'C-101',
                ],
                'property' => [
                    'id' => '3',
                    'name' => 'Commercial Building C',
                    'address' => 'Jl. Gatot Subroto No. 789, Jakarta',
                ],
                'client' => [
                    'id' => '2',
                    'name' => 'XYZ Utilities',
                ],
                // Similar readings and maintenance history would be here
            ],
        ];
        
        if (!isset($meters[$meterId])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Meter not found',
            ], 404);
        }
        
        $meter = $meters[$meterId];
        
        // Check if the client user has access to this meter
        if ($user['role'] === 'client' && $meter['client_id'] !== $user['id']) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access to this meter',
            ], 403);
        }
        
        // Check if the customer user has access to this meter
        if ($user['role'] === 'customer' && $user['id'] !== $meter['customer_id']) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access to this meter',
            ], 403);
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Meter retrieved successfully',
            'data' => $meter,
        ]);
    }

    public function createMeter(Request $request, Response $response): Response
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
        if (!isset($data['serial_number']) || !isset($data['property_id']) || !isset($data['customer_id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Serial number, property ID, and customer ID are required',
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
        
        // In a real application, you would save the meter to the database
        // For demo purposes, we'll just return a success response with mock data
        $meter = [
            'id' => 'M-' . rand(1000, 9999), // In a real application, this would be generated by the database
            'client_id' => $clientId,
            'property_id' => $data['property_id'],
            'customer_id' => $data['customer_id'],
            'serial_number' => $data['serial_number'],
            'installation_date' => date('Y-m-d'),
            'last_reading_date' => null,
            'last_reading_value' => 0,
            'status' => 'active',
            'customer' => [
                'id' => $data['customer_id'],
                'name' => 'Customer Name', // In a real application, this would be fetched from the database
                'unit' => 'Unit Number', // In a real application, this would be fetched from the database
            ],
            'property' => [
                'id' => $data['property_id'],
                'name' => 'Property Name', // In a real application, this would be fetched from the database
            ],
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Meter created successfully',
            'data' => $meter,
        ], 201);
    }

    public function updateMeter(Request $request, Response $response, array $args): Response
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
                'message' => 'Meter ID is required',
            ], 400);
        }
        
        $meterId = $args['id'];
        $data = $request->getParsedBody();
        
        // In a real application, you would fetch the meter from the database
        // and check if the client user has access to it
        
        // For demo purposes, we'll just return a success response with mock data
        $meter = [
            'id' => $meterId,
            'client_id' => $user['role'] === 'client' ? $user['id'] : ($data['client_id'] ?? '1'),
            'property_id' => $data['property_id'] ?? '1',
            'customer_id' => $data['customer_id'] ?? '1',
            'serial_number' => $data['serial_number'] ?? 'SN12345',
            'installation_date' => $data['installation_date'] ?? '2023-01-25',
            'last_reading_date' => $data['last_reading_date'] ?? '2023-06-25',
            'last_reading_value' => $data['last_reading_value'] ?? 150.5,
            'status' => $data['status'] ?? 'active',
            'customer' => [
                'id' => $data['customer_id'] ?? '1',
                'name' => 'Customer Name', // In a real application, this would be fetched from the database
                'unit' => 'Unit Number', // In a real application, this would be fetched from the database
            ],
            'property' => [
                'id' => $data['property_id'] ?? '1',
                'name' => 'Property Name', // In a real application, this would be fetched from the database
            ],
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Meter updated successfully',
            'data' => $meter,
        ]);
    }

    public function deleteMeter(Request $request, Response $response, array $args): Response
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
                'message' => 'Meter ID is required',
            ], 400);
        }
        
        $meterId = $args['id'];
        
        // In a real application, you would fetch the meter from the database
        // and check if the client user has access to it before deleting
        
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Meter deleted successfully',
        ]);
    }

    public function submitReading(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client, superadmin, or the customer who owns the meter
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
                'message' => 'Meter ID is required',
            ], 400);
        }
        
        $meterId = $args['id'];
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['reading_value']) || !isset($data['reading_date'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Reading value and reading date are required',
            ], 400);
        }
        
        // In a real application, you would fetch the meter from the database
        // and check if the user has access to it
        
        // For demo purposes, we'll just return a success response with mock data
        $reading = [
            'meter_id' => $meterId,
            'date' => $data['reading_date'],
            'value' => (float) $data['reading_value'],
            'consumption' => 25.2, // In a real application, this would be calculated
            'status' => 'pending_verification',
            'submitted_by' => $user['role'],
            'submitted_at' => date('Y-m-d\TH:i:s\Z'),
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Reading submitted successfully',
            'data' => $reading,
        ], 201);
    }

    public function verifyReading(Request $request, Response $response, array $args): Response
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
        if (!isset($args['id']) || !isset($args['reading_id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Meter ID and reading ID are required',
            ], 400);
        }
        
        $meterId = $args['id'];
        $readingId = $args['reading_id'];
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['status'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Status is required',
            ], 400);
        }
        
        // Check if status is valid
        if (!in_array($data['status'], ['verified', 'rejected'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Status must be either "verified" or "rejected"',
            ], 400);
        }
        
        // In a real application, you would fetch the meter and reading from the database
        // and check if the client user has access to it
        
        // For demo purposes, we'll just return a success response with mock data
        $reading = [
            'id' => $readingId,
            'meter_id' => $meterId,
            'date' => '2023-06-25',
            'value' => 150.5,
            'consumption' => 25.2,
            'status' => $data['status'],
            'verified_by' => $user['id'],
            'verified_at' => date('Y-m-d\TH:i:s\Z'),
            'notes' => $data['notes'] ?? null,
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Reading ' . $data['status'] . ' successfully',
            'data' => $reading,
        ]);
    }

    public function getMeterReadings(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a client, superadmin, or the customer who owns the meter
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
                'message' => 'Meter ID is required',
            ], 400);
        }
        
        $meterId = $args['id'];
        
        // In a real application, you would fetch the meter readings from the database
        // and check if the user has access to it
        
        // For demo purposes, we'll return mock data
        $readings = [
            [
                'id' => 'R-101',
                'meter_id' => $meterId,
                'date' => '2023-01-31',
                'value' => 25.5,
                'consumption' => 25.5,
                'status' => 'verified',
                'submitted_by' => 'client',
                'submitted_at' => '2023-01-31T10:30:00Z',
                'verified_by' => '1',
                'verified_at' => '2023-01-31T11:15:00Z',
            ],
            [
                'id' => 'R-102',
                'meter_id' => $meterId,
                'date' => '2023-02-28',
                'value' => 50.8,
                'consumption' => 25.3,
                'status' => 'verified',
                'submitted_by' => 'client',
                'submitted_at' => '2023-02-28T09:45:00Z',
                'verified_by' => '1',
                'verified_at' => '2023-02-28T10:30:00Z',
            ],
            [
                'id' => 'R-103',
                'meter_id' => $meterId,
                'date' => '2023-03-31',
                'value' => 75.2,
                'consumption' => 24.4,
                'status' => 'verified',
                'submitted_by' => 'client',
                'submitted_at' => '2023-03-31T11:00:00Z',
                'verified_by' => '1',
                'verified_at' => '2023-03-31T11:45:00Z',
            ],
            [
                'id' => 'R-104',
                'meter_id' => $meterId,
                'date' => '2023-04-30',
                'value' => 100.6,
                'consumption' => 25.4,
                'status' => 'verified',
                'submitted_by' => 'customer',
                'submitted_at' => '2023-04-30T10:15:00Z',
                'verified_by' => '1',
                'verified_at' => '2023-04-30T11:00:00Z',
            ],
            [
                'id' => 'R-105',
                'meter_id' => $meterId,
                'date' => '2023-05-31',
                'value' => 125.3,
                'consumption' => 24.7,
                'status' => 'verified',
                'submitted_by' => 'customer',
                'submitted_at' => '2023-05-31T09:30:00Z',
                'verified_by' => '1',
                'verified_at' => '2023-05-31T10:15:00Z',
            ],
            [
                'id' => 'R-106',
                'meter_id' => $meterId,
                'date' => '2023-06-25',
                'value' => 150.5,
                'consumption' => 25.2,
                'status' => 'verified',
                'submitted_by' => 'customer',
                'submitted_at' => '2023-06-25T10:45:00Z',
                'verified_by' => '1',
                'verified_at' => '2023-06-25T11:30:00Z',
            ],
        ];
        
        // Get query parameters for filtering
        $status = isset($request->getQueryParams()['status']) ? $request->getQueryParams()['status'] : null;
        $startDate = isset($request->getQueryParams()['start_date']) ? $request->getQueryParams()['start_date'] : null;
        $endDate = isset($request->getQueryParams()['end_date']) ? $request->getQueryParams()['end_date'] : null;
        
        // Filter readings by status if provided
        if ($status) {
            $readings = array_filter($readings, function ($reading) use ($status) {
                return $reading['status'] === $status;
            });
        }
        
        // Filter readings by start date if provided
        if ($startDate) {
            $readings = array_filter($readings, function ($reading) use ($startDate) {
                return strtotime($reading['date']) >= strtotime($startDate);
            });
        }
        
        // Filter readings by end date if provided
        if ($endDate) {
            $readings = array_filter($readings, function ($reading) use ($endDate) {
                return strtotime($reading['date']) <= strtotime($endDate);
            });
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Meter readings retrieved successfully',
            'data' => array_values($readings), // Reset array keys
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