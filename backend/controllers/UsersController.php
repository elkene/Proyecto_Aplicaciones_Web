<?php

class UsersController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['email']) || !isset($data['password']) || !isset($data['name'])) {
                http_response_code(400);
                return ['error' => 'Missing required fields'];
            }

            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                return ['error' => 'Invalid email format'];
            }

            // Validate password strength
            if (strlen($data['password']) < 8) {
                http_response_code(400);
                return ['error' => 'Password must be at least 8 characters'];
            }

            if (!preg_match('/[A-Z]/', $data['password'])) {
                http_response_code(400);
                return ['error' => 'Password must contain at least one uppercase letter'];
            }

            if (!preg_match('/[0-9]/', $data['password'])) {
                http_response_code(400);
                return ['error' => 'Password must contain at least one number'];
            }

            // Check if email already exists
            if ($this->userModel->emailExists($data['email'])) {
                http_response_code(400);
                return ['error' => 'Email already registered'];
            }

            $user = $this->userModel->register($data['email'], $data['password'], $data['name']);

            // Generate JWT token
            $token = JWT::encode([
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'],
                'iat' => time(),
                'exp' => time() + (24 * 60 * 60) // 24 hours
            ]);

            http_response_code(201);
            return [
                'user' => $user,
                'token' => $token
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function login() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['email']) || !isset($data['password'])) {
                http_response_code(400);
                return ['error' => 'Missing email or password'];
            }

            $user = $this->userModel->login($data['email'], $data['password']);

            // Generate JWT token
            $token = JWT::encode([
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'],
                'iat' => time(),
                'exp' => time() + (24 * 60 * 60) // 24 hours
            ]);

            return [
                'user' => $user,
                'token' => $token
            ];
        } catch (Exception $e) {
            http_response_code(401);
            return ['error' => $e->getMessage()];
        }
    }

    public function getProfile() {
        try {
            $user = AuthMiddleware::authenticate();
            $profile = $this->userModel->getById($user['id']);

            if (!$profile) {
                http_response_code(404);
                return ['error' => 'User not found'];
            }

            return $profile;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function updateProfile() {
        try {
            $user = AuthMiddleware::authenticate();
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                http_response_code(400);
                return ['error' => 'Invalid data'];
            }

            $this->userModel->updateProfile($user['id'], $data);
            $updatedUser = $this->userModel->getById($user['id']);

            return $updatedUser;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function changePassword() {
        try {
            $user = AuthMiddleware::authenticate();
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['oldPassword']) || !isset($data['newPassword'])) {
                http_response_code(400);
                return ['error' => 'Missing required fields'];
            }

            $this->userModel->changePassword($user['id'], $data['oldPassword'], $data['newPassword']);

            return ['message' => 'Password changed successfully'];
        } catch (Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
}
