<?php

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($email, $password, $name) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $id = $this->generateUUID();

            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (id, email, password, name, role, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'user', NOW(), NOW())
            ");

            if (!$stmt->execute([$id, $email, $hashedPassword, $name])) {
                throw new Exception("Failed to register user");
            }

            return [
                'id' => $id,
                'email' => $email,
                'name' => $name,
                'role' => 'user'
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception("Invalid credentials");
            }

            if (!password_verify($password, $user['password'])) {
                throw new Exception("Invalid credentials");
            }

            unset($user['password']);
            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if ($user) {
                unset($user['password']);
            }

            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateProfile($id, $data) {
        try {
            $allowedFields = ['name', 'location', 'phone', 'bio', 'avatar'];
            $fields = [];
            $values = [];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
            }

            if (empty($fields)) {
                return false;
            }

            $values[] = $id;
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function changePassword($id, $oldPassword, $newPassword) {
        try {
            $user = $this->getById($id);
            if (!$user) {
                throw new Exception("User not found");
            }

            $stmt = $this->db->prepare("SELECT password FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();

            if (!password_verify($oldPassword, $result['password'])) {
                throw new Exception("Invalid current password");
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE {$this->table} SET password = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$hashedPassword, $id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function emailExists($email, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
            $params = [$email];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function generateUUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
