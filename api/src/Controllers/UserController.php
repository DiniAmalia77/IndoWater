<?php

declare(strict_types=1);

namespace IndoWater\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use IndoWater\Api\Services\UserService;
use IndoWater\Api\Exceptions\ValidationException;
use IndoWater\Api\Exceptions\NotFoundException;

class UserController extends BaseController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $pagination = $this->getPaginationParams($queryParams);
            $search = $this->getSearchParams($queryParams);
            $filters = $this->getFilterParams($queryParams, ['role', 'status']);

            $result = $this->userService->getUsers($pagination, $search, $filters);

            return $this->jsonResponse($response, [
                'status' => 'success',
                'data' => $this->buildPaginatedResponse(
                    $result['users'],
                    $result['total'],
                    $pagination
                ),
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred while fetching users',
            ], 500);
        }
    }

    public function show(Request $request, Response $response): Response
    {
        try {
            $id = $request->getAttribute('id');
            $this->validateUuid($id);

            $user = $this->userService->getUserById($id);

            return $this->jsonResponse($response, [
                'status' => 'success',
                'data' => ['user' => $user],
            ]);
        } catch (NotFoundException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred while fetching user',
            ], 500);
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            $this->validateRequired($data, ['name', 'email', 'password', 'role']);
            $this->validateEmail($data['email']);
            
            $allowedData = $this->sanitizeInput($data, [
                'name', 'email', 'password', 'phone', 'role', 'status'
            ]);

            $user = $this->userService->createUser($allowedData);

            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'User created successfully',
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
                'message' => 'An error occurred while creating user',
            ], 500);
        }
    }

    public function update(Request $request, Response $response): Response
    {
        try {
            $id = $request->getAttribute('id');
            $this->validateUuid($id);

            $data = $request->getParsedBody();
            
            $allowedData = $this->sanitizeInput($data, [
                'name', 'email', 'phone', 'role', 'status'
            ]);

            if (isset($allowedData['email'])) {
                $this->validateEmail($allowedData['email']);
            }

            $user = $this->userService->updateUser($id, $allowedData);

            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => ['user' => $user],
            ]);
        } catch (NotFoundException $e) {
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
                'message' => 'An error occurred while updating user',
            ], 500);
        }
    }

    public function delete(Request $request, Response $response): Response
    {
        try {
            $id = $request->getAttribute('id');
            $this->validateUuid($id);

            $this->userService->deleteUser($id);

            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'User deleted successfully',
            ]);
        } catch (NotFoundException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred while deleting user',
            ], 500);
        }
    }

    public function me(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');

            return $this->jsonResponse($response, [
                'status' => 'success',
                'data' => ['user' => $user],
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'An error occurred while fetching user profile',
            ], 500);
        }
    }

    public function updateProfile(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $data = $request->getParsedBody();
            
            $allowedData = $this->sanitizeInput($data, ['name', 'email', 'phone']);

            if (isset($allowedData['email'])) {
                $this->validateEmail($allowedData['email']);
            }

            $updatedUser = $this->userService->updateUser($user->getId(), $allowedData);

            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => ['user' => $updatedUser],
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
                'message' => 'An error occurred while updating profile',
            ], 500);
        }
    }

    public function updatePassword(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $data = $request->getParsedBody();
            
            $this->validateRequired($data, ['current_password', 'new_password']);
            
            if (strlen($data['new_password']) < 8) {
                throw new ValidationException('New password must be at least 8 characters long');
            }

            $this->userService->updatePassword(
                $user->getId(),
                $data['current_password'],
                $data['new_password']
            );

            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Password updated successfully',
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
                'message' => 'An error occurred while updating password',
            ], 500);
        }
    }
}