<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Email and password are required',
            ], 400);
        }
        
        $email = $data['email'];
        $password = $data['password'];
        
        // For demo purposes, we'll use hardcoded credentials
        // In a real application, you would validate against a database
        if ($email === 'admin@example.com' && $password === 'password') {
            $user = [
                'id' => '1',
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'superadmin',
                'status' => 'active',
            ];
            
            $token = $this->generateToken($user);
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ]);
        } elseif ($email === 'client@example.com' && $password === 'password') {
            $user = [
                'id' => '2',
                'name' => 'Client User',
                'email' => 'client@example.com',
                'role' => 'client',
                'status' => 'active',
            ];
            
            $token = $this->generateToken($user);
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ]);
        } elseif ($email === 'customer@example.com' && $password === 'password') {
            $user = [
                'id' => '3',
                'name' => 'Customer User',
                'email' => 'customer@example.com',
                'role' => 'customer',
                'status' => 'active',
            ];
            
            $token = $this->generateToken($user);
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ]);
        }
        
        return $this->jsonResponse($response, [
            'status' => 'error',
            'message' => 'Invalid email or password',
        ], 401);
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['password_confirmation'])) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Name, email, password, and password confirmation are required',
            ], 400);
        }
        
        // Check if passwords match
        if ($data['password'] !== $data['password_confirmation']) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Passwords do not match',
            ], 400);
        }
        
        // In a real application, you would save the user to a database
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'Registration successful. Please check your email to verify your account.',
        ]);
    }

    public function logout(Request $request, Response $response): Response
    {
        // In a real application, you might want to invalidate the token
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'Logout successful',
        ]);
    }

    public function forgotPassword(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['email'])) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Email is required',
            ], 400);
        }
        
        // In a real application, you would send a password reset email
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'Password reset link has been sent to your email',
        ]);
    }

    public function resetPassword(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['token']) || !isset($data['password']) || !isset($data['password_confirmation'])) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Token, password, and password confirmation are required',
            ], 400);
        }
        
        // Check if passwords match
        if ($data['password'] !== $data['password_confirmation']) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Passwords do not match',
            ], 400);
        }
        
        // In a real application, you would validate the token and update the password
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'Password has been reset successfully',
        ]);
    }

    public function verifyEmail(Request $request, Response $response, array $args): Response
    {
        // Validate input
        if (!isset($args['token'])) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Token is required',
            ], 400);
        }
        
        // In a real application, you would validate the token and verify the email
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'Email verified successfully',
        ]);
    }

    public function resendVerification(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['email'])) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Email is required',
            ], 400);
        }
        
        // In a real application, you would send a verification email
        // For demo purposes, we'll just return a success response
        
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'Verification email has been resent',
        ]);
    }
    
    public function getUser(Request $request, Response $response): Response
    {
        // Get the user from the request attribute (set by the JWT middleware)
        $user = $request->getAttribute('user');
        
        if (!$user) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
        
        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => $user,
        ]);
    }

    private function generateToken(array $user): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // Token valid for 1 hour
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user' => $user,
        ];
        
        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}