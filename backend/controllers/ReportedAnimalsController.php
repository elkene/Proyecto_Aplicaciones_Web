<?php

class ReportedAnimalsController {
    private $model;

    public function __construct() {
        $this->model = new ReportedAnimal();
    }

    public function create() {
        try {
            $user = AuthMiddleware::authenticate();
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['type']) || !isset($data['latitude']) || !isset($data['longitude'])) {
                http_response_code(400);
                return ['error' => 'Missing required fields'];
            }

            $report = $this->model->create(
                $user['id'],
                $data['type'],
                $data['description'] ?? '',
                $data['latitude'],
                $data['longitude'],
                $data['phone'] ?? '',
                $data['image'] ?? ''
            );

            http_response_code(201);
            return $report;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function getAll() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;

            $filters = [];
            if (isset($_GET['status'])) $filters['status'] = $_GET['status'];

            $result = $this->model->getAll($page, $limit, $filters);

            return $result;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function getById($id) {
        try {
            $report = $this->model->getById($id);

            if (!$report) {
                http_response_code(404);
                return ['error' => 'Report not found'];
            }

            return $report;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function getNearby() {
        try {
            $latitude = $_GET['latitude'] ?? null;
            $longitude = $_GET['longitude'] ?? null;
            $radius = $_GET['radius'] ?? 5;

            if (!$latitude || !$longitude) {
                http_response_code(400);
                return ['error' => 'Missing latitude or longitude'];
            }

            $reports = $this->model->getNearby($latitude, $longitude, $radius);
            return $reports;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function update($id) {
        try {
            $user = AuthMiddleware::authenticate();
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                http_response_code(400);
                return ['error' => 'No data provided'];
            }

            $report = $this->model->getById($id);
            if (!$report) {
                http_response_code(404);
                return ['error' => 'Report not found'];
            }

            // Only the report creator can edit details (type, description, phone, image)
            if ($user['id'] !== $report['user_id']) {
                http_response_code(403);
                return ['error' => 'Solo el creador del reporte puede editarlo'];
            }

            // Update report details
            if (isset($data['type'])) {
                $report['type'] = $data['type'];
            }
            if (isset($data['description'])) {
                $report['description'] = $data['description'];
            }
            if (isset($data['phone'])) {
                $report['phone'] = $data['phone'];
            }
            if (isset($data['image'])) {
                $report['image'] = $data['image'];
            }

            // Update in database
            $this->model->updateFull(
                $id,
                $report['type'],
                $report['description'],
                $report['phone'],
                $report['image'],
                $report['status']
            );

            return $this->model->getById($id);
        } catch (Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }

    public function updateStatus($id) {
        try {
            $user = AuthMiddleware::authenticate();
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['status'])) {
                http_response_code(400);
                return ['error' => 'Missing status'];
            }

            $this->model->updateStatus($id, $data['status'], $data['notes'] ?? '');

            // Add update record
            $this->model->addUpdate(
                $id,
                $user['id'],
                'status_change',
                'Estado actualizado a: ' . $data['status'],
                $data['status']
            );

            return $this->model->getById($id);
        } catch (Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }

    public function addUpdate($id) {
        try {
            $user = AuthMiddleware::authenticate();
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['content'])) {
                http_response_code(400);
                return ['error' => 'Missing content'];
            }

            $update = $this->model->addUpdate(
                $id,
                $user['id'],
                $data['type'] ?? 'comment',
                $data['content'],
                $data['new_status'] ?? null
            );

            http_response_code(201);
            return $update;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function delete($id) {
        try {
            $user = AuthMiddleware::authenticate();
            $report = $this->model->getById($id);

            if (!$report) {
                http_response_code(404);
                return ['error' => 'Report not found'];
            }

            // Only admin can delete reports
            if ($user['email'] !== 'admin@pawmatch.com') {
                http_response_code(403);
                return ['error' => 'Solo el admin puede eliminar reportes'];
            }

            $this->model->delete($id);
            return ['message' => 'Report deleted'];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
}
