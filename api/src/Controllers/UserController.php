<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getMe(Request $request, Response $response): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        if (!$user) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }
        
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => $user,
        ]);
    }

    public function updateProfile(Request $request, Response $response): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        if (!$user) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }
        
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['name']) || !isset($data['email'])) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Name and email are required',
            ], 400);
        }
        
        // In a real application, you would update the user in the database
        // For demo purposes, we'll just return the updated user
        
        $updatedUser = [
            'id' => $user['id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $user['role'],
            'status' => $user['status'],
        ];
        
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $updatedUser,
        ]);
    }

    public function updatePassword(Request $request, Response $response): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        if (!$user) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }
        
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['current_password']) || !isset($data['password']) || !isset($data['password_confirmation'])) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Current password, new password, and password confirmation are required',
            ], 400);
        }
        
        // Check if passwords match
        if ($data['password'] !== $data['password_confirmation']) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Passwords do not match',
            ], 400);
        }
        
        // In a real application, you would validate the current password and update the password
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'Password updated successfully',
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