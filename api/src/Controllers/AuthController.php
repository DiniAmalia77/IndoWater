<?php

declare(strict_types=1);

namespace IndoWater\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use IndoWater\Api\Services\AuthService;
use IndoWater\Api\Exceptions\AuthenticationException;
use IndoWater\Api\Exceptions\ValidationException;
use IndoWater\Api\Utils\JWT;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            $this->validateRequired($data, ['email', 'password']);
            
            $result = $this->authService->login($data['email'], $data['password']);
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Login successful',
                'data' => $result,
            ]);
        } catch (AuthenticationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getCode());
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred during login',
            ], 500);
        }
    }

    public function register(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            $this->validateRequired($data, ['name', 'email', 'password']);
            $this->validateEmail($data['email']);
            $this->validatePassword($data['password']);
            
            $user = $this->authService->register($data);
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Registration successful. Please check your email for verification.',
                'data' => ['user' => $user],
            ], 201);
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getCode());
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred during registration',
            ], 500);
        }
    }

    public function refresh(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            $this->validateRequired($data, ['refresh_token']);
            
            $result = $this->authService->refresh($data['refresh_token']);
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Token refreshed successfully',
                'data' => $result,
            ]);
        } catch (AuthenticationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred during token refresh',
            ], 500);
        }
    }

    public function logout(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            
            if ($user) {
                $this->authService->logout($user->getId());
            }
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred during logout',
            ], 500);
        }
    }

    public function forgotPassword(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            $this->validateRequired($data, ['email']);
            $this->validateEmail($data['email']);
            
            $this->authService->forgotPassword($data['email']);
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'If the email exists, a password reset link has been sent.',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getCode());
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
            ], 500);
        }
    }

    public function resetPassword(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            $this->validateRequired($data, ['token', 'password']);
            $this->validatePassword($data['password']);
            
            $this->authService->resetPassword($data['token'], $data['password']);
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Password reset successful',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getCode());
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred while resetting password',
            ], 500);
        }
    }

    public function verifyEmail(Request $request, Response $response): Response
    {
        try {
            $token = $request->getAttribute('token');
            
            $this->authService->verifyEmail($token);
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Email verified successfully',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred while verifying email',
            ], 500);
        }
    }

    public function resendVerification(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            $this->validateRequired($data, ['email']);
            $this->validateEmail($data['email']);
            
            $this->authService->resendVerification($data['email']);
            
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Verification email sent',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getCode());
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred while sending verification email',
            ], 500);
        }
    }

    private function validatePassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new ValidationException('Password must be at least 8 characters long');
        }
    }
}