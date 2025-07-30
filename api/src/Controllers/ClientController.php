<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ClientController
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getClients(Request $request, Response $response): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a superadmin
        if ($user['role'] !== 'superadmin') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // In a real application, you would fetch clients from the database
        // For demo purposes, we'll return mock data
        $clients = [
            [
                'id' => '1',
                'name' => 'ABC Water Company',
                'email' => 'contact@abcwater.com',
                'phone' => '+62123456789',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'status' => 'active',
                'created_at' => '2023-01-15T08:30:00Z',
                'properties_count' => 5,
                'customers_count' => 120,
            ],
            [
                'id' => '2',
                'name' => 'XYZ Utilities',
                'email' => 'info@xyzutilities.com',
                'phone' => '+62987654321',
                'address' => 'Jl. Thamrin No. 456, Jakarta',
                'status' => 'active',
                'created_at' => '2023-02-20T10:15:00Z',
                'properties_count' => 3,
                'customers_count' => 85,
            ],
            [
                'id' => '3',
                'name' => 'Hydro Solutions',
                'email' => 'support@hydrosolutions.com',
                'phone' => '+62456789123',
                'address' => 'Jl. Gatot Subroto No. 789, Jakarta',
                'status' => 'inactive',
                'created_at' => '2023-03-10T14:45:00Z',
                'properties_count' => 0,
                'customers_count' => 0,
            ],
        ];
        
        // Get query parameters for pagination
        $page = isset($request->getQueryParams()['page']) ? (int) $request->getQueryParams()['page'] : 1;
        $limit = isset($request->getQueryParams()['limit']) ? (int) $request->getQueryParams()['limit'] : 10;
        
        // Get query parameters for filtering
        $status = isset($request->getQueryParams()['status']) ? $request->getQueryParams()['status'] : null;
        
        // Filter clients by status if provided
        if ($status) {
            $clients = array_filter($clients, function ($client) use ($status) {
                return $client['status'] === $status;
            });
        }
        
        // Calculate pagination
        $total = count($clients);
        $offset = ($page - 1) * $limit;
        $clients = array_slice($clients, $offset, $limit);
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Clients retrieved successfully',
            'data' => [
                'clients' => $clients,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil($total / $limit),
                ],
            ],
        ]);
    }

    public function getClient(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a superadmin
        if ($user['role'] !== 'superadmin') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // Validate input
        if (!isset($args['id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Client ID is required',
            ], 400);
        }
        
        $clientId = $args['id'];
        
        // In a real application, you would fetch the client from the database
        // For demo purposes, we'll return mock data
        $clients = [
            '1' => [
                'id' => '1',
                'name' => 'ABC Water Company',
                'email' => 'contact@abcwater.com',
                'phone' => '+62123456789',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'status' => 'active',
                'created_at' => '2023-01-15T08:30:00Z',
                'properties' => [
                    [
                        'id' => '1',
                        'name' => 'Apartment Complex A',
                        'address' => 'Jl. Sudirman No. 123, Jakarta',
                        'customers_count' => 50,
                    ],
                    [
                        'id' => '2',
                        'name' => 'Residential Area B',
                        'address' => 'Jl. Thamrin No. 456, Jakarta',
                        'customers_count' => 70,
                    ],
                ],
                'stats' => [
                    'total_properties' => 5,
                    'total_customers' => 120,
                    'active_meters' => 118,
                    'inactive_meters' => 2,
                    'total_consumption' => 15000,
                    'total_revenue' => 75000000,
                ],
            ],
            '2' => [
                'id' => '2',
                'name' => 'XYZ Utilities',
                'email' => 'info@xyzutilities.com',
                'phone' => '+62987654321',
                'address' => 'Jl. Thamrin No. 456, Jakarta',
                'status' => 'active',
                'created_at' => '2023-02-20T10:15:00Z',
                'properties' => [
                    [
                        'id' => '3',
                        'name' => 'Commercial Building C',
                        'address' => 'Jl. Gatot Subroto No. 789, Jakarta',
                        'customers_count' => 35,
                    ],
                    [
                        'id' => '4',
                        'name' => 'Residential Area D',
                        'address' => 'Jl. Rasuna Said No. 101, Jakarta',
                        'customers_count' => 50,
                    ],
                ],
                'stats' => [
                    'total_properties' => 3,
                    'total_customers' => 85,
                    'active_meters' => 83,
                    'inactive_meters' => 2,
                    'total_consumption' => 12000,
                    'total_revenue' => 60000000,
                ],
            ],
            '3' => [
                'id' => '3',
                'name' => 'Hydro Solutions',
                'email' => 'support@hydrosolutions.com',
                'phone' => '+62456789123',
                'address' => 'Jl. Gatot Subroto No. 789, Jakarta',
                'status' => 'inactive',
                'created_at' => '2023-03-10T14:45:00Z',
                'properties' => [],
                'stats' => [
                    'total_properties' => 0,
                    'total_customers' => 0,
                    'active_meters' => 0,
                    'inactive_meters' => 0,
                    'total_consumption' => 0,
                    'total_revenue' => 0,
                ],
            ],
        ];
        
        if (!isset($clients[$clientId])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Client retrieved successfully',
            'data' => $clients[$clientId],
        ]);
    }

    public function createClient(Request $request, Response $response): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a superadmin
        if ($user['role'] !== 'superadmin') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['phone']) || !isset($data['address'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Name, email, phone, and address are required',
            ], 400);
        }
        
        // In a real application, you would save the client to the database
        // For demo purposes, we'll just return a success response with mock data
        $client = [
            'id' => '4', // In a real application, this would be generated by the database
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'status' => 'active',
            'created_at' => date('Y-m-d\TH:i:s\Z'),
            'properties_count' => 0,
            'customers_count' => 0,
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Client created successfully',
            'data' => $client,
        ], 201);
    }

    public function updateClient(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a superadmin
        if ($user['role'] !== 'superadmin') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // Validate input
        if (!isset($args['id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Client ID is required',
            ], 400);
        }
        
        $clientId = $args['id'];
        $data = $request->getParsedBody();
        
        // In a real application, you would fetch the client from the database and update it
        // For demo purposes, we'll just return a success response with mock data
        $client = [
            'id' => $clientId,
            'name' => $data['name'] ?? 'Updated Client',
            'email' => $data['email'] ?? 'updated@example.com',
            'phone' => $data['phone'] ?? '+62123456789',
            'address' => $data['address'] ?? 'Updated Address',
            'status' => $data['status'] ?? 'active',
            'created_at' => '2023-01-15T08:30:00Z',
            'properties_count' => 0,
            'customers_count' => 0,
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Client updated successfully',
            'data' => $client,
        ]);
    }

    public function deleteClient(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a superadmin
        if ($user['role'] !== 'superadmin') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // Validate input
        if (!isset($args['id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Client ID is required',
            ], 400);
        }
        
        $clientId = $args['id'];
        
        // In a real application, you would delete the client from the database
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Client deleted successfully',
        ]);
    }

    public function getClientStats(Request $request, Response $response, array $args): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        // Check if user is a superadmin
        if ($user['role'] !== 'superadmin') {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        
        // Validate input
        if (!isset($args['id'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Client ID is required',
            ], 400);
        }
        
        $clientId = $args['id'];
        
        // In a real application, you would fetch the client stats from the database
        // For demo purposes, we'll return mock data
        $stats = [
            'total_properties' => 5,
            'total_customers' => 120,
            'active_meters' => 118,
            'inactive_meters' => 2,
            'total_consumption' => 15000,
            'total_revenue' => 75000000,
            'monthly_stats' => [
                [
                    'month' => 'January',
                    'consumption' => 1200,
                    'revenue' => 6000000,
                ],
                [
                    'month' => 'February',
                    'consumption' => 1300,
                    'revenue' => 6500000,
                ],
                [
                    'month' => 'March',
                    'consumption' => 1250,
                    'revenue' => 6250000,
                ],
                [
                    'month' => 'April',
                    'consumption' => 1350,
                    'revenue' => 6750000,
                ],
                [
                    'month' => 'May',
                    'consumption' => 1400,
                    'revenue' => 7000000,
                ],
                [
                    'month' => 'June',
                    'consumption' => 1450,
                    'revenue' => 7250000,
                ],
            ],
        ];
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Client stats retrieved successfully',
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