<?php

class AuthMiddleware {
    public static function authenticate() {
        $token = JWT::getTokenFromHeader();

        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Missing authorization token']);
            exit;
        }

        try {
            $payload = JWT::decode($token);
            $_SESSION['user'] = $payload;
            return $payload;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public static function requireAdmin() {
        $user = self::authenticate();

        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            exit;
        }

        return $user;
    }

    public static function getUser() {
        $token = JWT::getTokenFromHeader();

        if (!$token) {
            return null;
        }

        try {
            return JWT::decode($token);
        } catch (Exception $e) {
            return null;
        }
    }
}
