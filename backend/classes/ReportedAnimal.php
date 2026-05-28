<?php

class ReportedAnimal {
    private $db;
    private $table = 'reported_animals';
    private $updates_table = 'animal_report_updates';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $type, $description, $latitude, $longitude, $phone = '', $image = '') {
        try {
            $id = $this->generateUUID();

            $stmt = $this->db->prepare("
                INSERT INTO {$this->table}
                (id, user_id, type, description, latitude, longitude, phone, image, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())
            ");

            if (!$stmt->execute([$id, $userId, $type, $description, $latitude, $longitude, $phone, $image])) {
                throw new Exception("Failed to create report");
            }

            return $this->getById($id);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAll($page = 1, $limit = 50, $filters = []) {
        try {
            $offset = (int)(($page - 1) * $limit);
            $limit = (int)$limit;

            $where = ['1=1'];
            $params = [];

            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }

            $whereClause = implode(' AND ', $where);

            $sql = "
                SELECT ra.*, u.name as reporter_name, u.email as reporter_email
                FROM {$this->table} ra
                JOIN users u ON ra.user_id = u.id
                WHERE $whereClause
                ORDER BY ra.created_at DESC
                LIMIT $offset, $limit
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $reports = $stmt->fetchAll();

            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $countResult = $countStmt->fetch();
            $total = $countResult['total'];

            return [
                'data' => $reports,
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

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT ra.*, u.name as reporter_name, u.email as reporter_email
                FROM {$this->table} ra
                JOIN users u ON ra.user_id = u.id
                WHERE ra.id = ?
            ");
            $stmt->execute([$id]);
            $report = $stmt->fetch();

            if (!$report) {
                return null;
            }

            // Get updates/comments
            $updatesStmt = $this->db->prepare("
                SELECT aru.*, u.name as user_name
                FROM {$this->updates_table} aru
                JOIN users u ON aru.user_id = u.id
                WHERE aru.report_id = ?
                ORDER BY aru.created_at ASC
            ");
            $updatesStmt->execute([$id]);
            $report['updates'] = $updatesStmt->fetchAll();

            return $report;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getNearby($latitude, $longitude, $radiusKm = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT ra.*, u.name as reporter_name,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
                FROM {$this->table} ra
                JOIN users u ON ra.user_id = u.id
                WHERE status != 'rescued'
                HAVING distance < ?
                ORDER BY distance ASC
                LIMIT 20
            ");

            $stmt->execute([$latitude, $longitude, $latitude, $radiusKm]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateFull($id, $type, $description, $phone = '', $image = '', $status = 'pending') {
        try {
            $validStatuses = ['pending', 'in_rescue', 'rescued', 'unknown'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception("Invalid status");
            }

            $sql = "UPDATE {$this->table} SET type = ?, description = ?, phone = ?, image = ?, status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$type, $description, $phone, $image, $status, $id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateStatus($id, $status, $notes = '') {
        try {
            $validStatuses = ['pending', 'in_rescue', 'rescued', 'unknown'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception("Invalid status");
            }

            $sql = "UPDATE {$this->table} SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$status, $notes, $id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function addUpdate($reportId, $userId, $updateType, $content, $newStatus = null) {
        try {
            $id = $this->generateUUID();

            $stmt = $this->db->prepare("
                INSERT INTO {$this->updates_table}
                (id, report_id, user_id, update_type, content, new_status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            if (!$stmt->execute([$id, $reportId, $userId, $updateType, $content, $newStatus])) {
                throw new Exception("Failed to add update");
            }

            return $this->getUpdateById($id);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getUpdateById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT aru.*, u.name as user_name
                FROM {$this->updates_table} aru
                JOIN users u ON aru.user_id = u.id
                WHERE aru.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
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
