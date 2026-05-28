<?php

class Adoption {
    private $db;
    private $table = 'adoption_requests';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $petId, $message = '') {
        try {
            $id = $this->generateUUID();

            // Check if user already requested this pet
            $stmt = $this->db->prepare("
                SELECT id FROM {$this->table}
                WHERE user_id = ? AND pet_id = ?
            ");
            $stmt->execute([$userId, $petId]);
            if ($stmt->fetch()) {
                throw new Exception("You have already requested this pet");
            }

            $stmt = $this->db->prepare("
                INSERT INTO {$this->table}
                (id, user_id, pet_id, message, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())
            ");

            if (!$stmt->execute([$id, $userId, $petId, $message])) {
                throw new Exception("Failed to create adoption request");
            }

            return $this->getById($id);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getByUser($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT ar.*, p.name as pet_name, p.image as pet_image
                FROM {$this->table} ar
                JOIN pets p ON ar.pet_id = p.id
                WHERE ar.user_id = ?
                ORDER BY ar.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateStatus($id, $status, $userId = null) {
        try {
            $validStatuses = ['pending', 'approved', 'rejected', 'completed'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception("Invalid status");
            }

            $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
            $params = [$status, $id];

            if ($userId) {
                $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
                $params = [$status, $id, $userId];
            }

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function delete($id, $userId = null) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $params = [$id];

            if ($userId) {
                $sql = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
                $params = [$id, $userId];
            }

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAll($filters = []) {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }

            $whereClause = empty($where) ? '1=1' : implode(' AND ', $where);

            $stmt = $this->db->prepare("
                SELECT ar.*, u.name as user_name, u.email as user_email, p.name as pet_name
                FROM {$this->table} ar
                JOIN users u ON ar.user_id = u.id
                JOIN pets p ON ar.pet_id = p.id
                WHERE $whereClause
                ORDER BY ar.created_at DESC
            ");

            $stmt->execute($params);
            return $stmt->fetchAll();
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
