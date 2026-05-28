<?php

class Donation {
    private $db;
    private $table = 'donations';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $amount, $currency = 'MXN', $message = '') {
        try {
            $id = $this->generateUUID();

            $stmt = $this->db->prepare("
                INSERT INTO {$this->table}
                (id, user_id, amount, currency, message, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            if (!$stmt->execute([$id, $userId, $amount, $currency, $message])) {
                throw new Exception("Failed to create donation");
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
                SELECT * FROM {$this->table}
                WHERE user_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAll($page = 1, $limit = 10) {
        try {
            $offset = (int)(($page - 1) * $limit);
            $limit = (int)$limit;

            $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT $offset, $limit";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $donations = $stmt->fetchAll();

            // Get total count
            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table}");
            $countStmt->execute();
            $countResult = $countStmt->fetch();
            $total = $countResult['total'];

            return [
                'data' => $donations,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getStats() {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) as totalDonations,
                    COALESCE(SUM(amount), 0) as totalAmount
                FROM {$this->table}
            ");
            $stmt->execute();
            return $stmt->fetch();
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
