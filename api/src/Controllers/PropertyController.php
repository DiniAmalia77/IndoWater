<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PropertyController
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getProperties(Request $request, Response $response): Response
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
        
        // In a real application, you would fetch properties from the database
        // For client users, you would only fetch properties belonging to their client account
        // For superadmin users, you would fetch all properties or filter by client ID if provided
        
        // For demo purposes, we'll return mock data
        $properties = [
            [
                'id' => '1',
                'client_id' => '1',
                'name' => 'Apartment Complex A',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'type' => 'apartment',
                'status' => 'active',
                'created_at' => '2023-01-20T09:30:00Z',
                'customers_count' => 50,
                'meters_count' => 50,
            ],
            [
                'id' => '2',
                'client_id' => '1',
                'name' => 'Residential Area B',
                'address' => 'Jl. Thamrin No. 456, Jakarta',
                'type' => 'residential',
                'status' => 'active',
                'created_at' => '2023-02-15T11:45:00Z',
                'customers_count' => 70,
                'meters_count' => 70,
            ],
            [
                'id' => '3',
                'client_id' => '2',
                'name' => 'Commercial Building C',
                'address' => 'Jl. Gatot Subroto No. 789, Jakarta',
                'type' => 'commercial',
                'status' => 'active',
                'created_at' => '2023-03-05T14:20:00Z',
                'customers_count' => 35,
                'meters_count' => 35,
            ],
        ];
        
        // Filter properties by client ID if the user is a client
        if ($user['role'] === 'client') {
            $clientId = $user['id'];
            $properties = array_filter($properties, function ($property) use ($clientId) {
                return $property['client_id'] === $clientId;
            });
        }
        
        // Filter properties by client ID if provided in query parameters (for superadmin)
        if ($user['role'] === 'superadmin' && isset($request->getQueryParams()['client_id'])) {
            $clientId = $request->getQueryParams()['client_id'];
            $properties = array_filter($properties, function ($property) use ($clientId) {
                return $property['client_id'] === $clientId;
            });
        }
        
        // Get query parameters for pagination
        $page = isset($request->getQueryParams()['page']) ? (int) $request->getQueryParams()['page'] : 1;
        $limit = isset($request->getQueryParams()['limit']) ? (int) $request->getQueryParams()['limit'] : 10;
        
        // Get query parameters for filtering
        $status = isset($request->getQueryParams()['status']) ? $request->getQueryParams()['status'] : null;
        $type = isset($request->getQueryParams()['type']) ? $request->getQueryParams()['type'] : null;
        
        // Filter properties by status if provided
        if ($status) {
            $properties = array_filter($properties, function ($property) use ($status) {
                return $property['status'] === $status;
            });
        }
        
        // Filter properties by type if provided
        if ($type) {
            $properties = array_filter($properties, function ($property) use ($type) {
                return $property['type'] === $type;
            });
        }
        
        // Calculate pagination
        $total = count($properties);
        $offset = ($page - 1) * $limit;
        $properties = array_slice($properties, $offset, $limit);
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Properties retrieved successfully',
            'data' => [
                'properties' => array_values($properties), // Reset array keys
                'pagination' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil($total / $limit),
                ],
            ],
        ]);
    }

    public function getProperty(Request $request, Response $response, array $args): Response
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
                'message' => 'Property ID is required',
            ], 400);
        }
        
        $propertyId = $args['id'];
        
        // In a real application, you would fetch the property from the database
        // For demo purposes, we'll return mock data
        $properties = [
            '1' => [
                'id' => '1',
                'client_id' => '1',
                'name' => 'Apartment Complex A',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'type' => 'apartment',
                'status' => 'active',
                'created_at' => '2023-01-20T09:30:00Z',
                'customers_count' => 50,
                'meters_count' => 50,
                'client' => [
                    'id' => '1',
                    'name' => 'ABC Water Company',
                ],
                'stats' => [
                    'total_customers' => 50,
                    'active_meters' => 49,
                    'inactive_meters' => 1,
                    'total_consumption' => 5000,
                    'total_revenue' => 25000000,
                ],
                'customers' => [
                    [
                        'id' => '1',
                        'name' => 'John Doe',
                        'unit' => 'A-101',
                        'meter_id' => 'M-101',
                        'status' => 'active',
                    ],
                    [
                        'id' => '2',
                        'name' => 'Jane Smith',
                        'unit' => 'A-102',
                        'meter_id' => 'M-102',
                        'status' => 'active',
                    ],
                    // More customers would be listed here
                ],
            ],
            '2' => [
                'id' => '2',
                'client_id' => '1',
                'name' => 'Residential Area B',
                'address' => 'Jl. Thamrin No. 456, Jakarta',
                'type' => 'residential',
                'status' => 'active',
                'created_at' => '2023-02-15T11:45:00Z',
                'customers_count' => 70,
                'meters_count' => 70,
                'client' => [
                    'id' => '1',
                    'name' => 'ABC Water Company',
                ],
                'stats' => [
                    'total_customers' => 70,
                    'active_meters' => 69,
                    'inactive_meters' => 1,
                    'total_consumption' => 7000,
                    'total_revenue' => 35000000,
                ],
                'customers' => [
                    [
                        'id' => '3',
                        'name' => 'Bob Johnson',
                        'unit' => 'B-101',
                        'meter_id' => 'M-201',
                        'status' => 'active',
                    ],
                    [
                        'id' => '4',
                        'name' => 'Alice Brown',
                        'unit' => 'B-102',
                        'meter_id' => 'M-202',
                        'status' => 'active',
                    ],
                    // More customers would be listed here
                ],
            ],
            '3' => [
                'id' => '3',
                'client_id' => '2',
                'name' => 'Commercial Building C',
                'address' => 'Jl. Gatot Subroto No. 789, Jakarta',
                'type' => 'commercial',
                'status' => 'active',
                'created_at' => '2023-03-05T14:20:00Z',
                'customers_count' => 35,
                'meters_count' => 35,
                'client' => [
                    'id' => '2',
                    'name' => 'XYZ Utilities',
                ],
                'stats' => [
                    'total_customers' => 35,
                    'active_meters' => 35,
                    'inactive_meters' => 0,
                    'total_consumption' => 8000,
                    'total_revenue' => 40000000,
                ],
                'customers' => [
                    [
                        'id' => '5',
                        'name' => 'Company X',
                        'unit' => 'C-101',
                        'meter_id' => 'M-301',
                        'status' => 'active',
                    ],
                    [
                        'id' => '6',
                        'name' => 'Company Y',
                        'unit' => 'C-102',
                        'meter_id' => 'M-302',
                        'status' => 'active',
                    ],
                    // More customers would be listed here
                ],
            ],
        ];
        
        if (!isset($properties[$propertyId])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Property not found',
            ], 404);
        }
        
        $property = $properties[$propertyId];
        
        // Check if the client user has access to this property
        if ($user['role'] === 'client' && $property['client_id'] !== $user['id']) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access to this property',
            ], 403);
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Property retrieved successfully',
            'data' => $property,
        ]);
    }

    public function createProperty(Request $request, Response $response): Response
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
        if (!isset($data['name']) || !isset($data['address']) || !isset($data['type'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Name, address, and type are required',
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
        
        // In a real application, you would save the property to the database
        // For demo purposes, we'll just return a success response with mock data
        $property = [
            'id' => '4', // In a real application, this would be generated by the database
            'client_id' => $clientId,
            'name' => $data['name'],
            'address' => $data['address'],
            'type' => $data['type'],
            'status' => 'active',
            'created_at' => date('Y-m-d\TH:i:s\Z'),
            'customers_count' => 0,
            'meters_count' => 0,
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Property created successfully',
            'data' => $property,
        ], 201);
    }

    public function updateProperty(Request $request, Response $response, array $args): Response
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
                'message' => 'Property ID is required',
            ], 400);
        }
        
        $propertyId = $args['id'];
        $data = $request->getParsedBody();
        
        // In a real application, you would fetch the property from the database
        // and check if the client user has access to it
        
        // For demo purposes, we'll just return a success response with mock data
        $property = [
            'id' => $propertyId,
            'client_id' => $user['role'] === 'client' ? $user['id'] : ($data['client_id'] ?? '1'),
            'name' => $data['name'] ?? 'Updated Property',
            'address' => $data['address'] ?? 'Updated Address',
            'type' => $data['type'] ?? 'residential',
            'status' => $data['status'] ?? 'active',
            'created_at' => '2023-01-20T09:30:00Z',
            'customers_count' => 0,
            'meters_count' => 0,
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Property updated successfully',
            'data' => $property,
        ]);
    }

    public function deleteProperty(Request $request, Response $response, array $args): Response
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
                'message' => 'Property ID is required',
            ], 400);
        }
        
        $propertyId = $args['id'];
        
        // In a real application, you would fetch the property from the database
        // and check if the client user has access to it before deleting
        
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Property deleted successfully',
        ]);
    }

    public function getPropertyStats(Request $request, Response $response, array $args): Response
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
                'message' => 'Property ID is required',
            ], 400);
        }
        
        $propertyId = $args['id'];
        
        // In a real application, you would fetch the property stats from the database
        // and check if the client user has access to it
        
        // For demo purposes, we'll return mock data
        $stats = [
            'total_customers' => 50,
            'active_meters' => 49,
            'inactive_meters' => 1,
            'total_consumption' => 5000,
            'total_revenue' => 25000000,
            'monthly_stats' => [
                [
                    'month' => 'January',
                    'consumption' => 400,
                    'revenue' => 2000000,
                ],
                [
                    'month' => 'February',
                    'consumption' => 420,
                    'revenue' => 2100000,
                ],
                [
                    'month' => 'March',
                    'consumption' => 410,
                    'revenue' => 2050000,
                ],
                [
                    'month' => 'April',
                    'consumption' => 430,
                    'revenue' => 2150000,
                ],
                [
                    'month' => 'May',
                    'consumption' => 440,
                    'revenue' => 2200000,
                ],
                [
                    'month' => 'June',
                    'consumption' => 450,
                    'revenue' => 2250000,
                ],
            ],
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Property stats retrieved successfully',
            'data' => $stats,
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