<?php

class AdoptionsController {
    private $adoptionModel;

    public function __construct() {
        $this->adoptionModel = new Adoption();
    }

    public function create() {
        try {
            $user = AuthMiddleware::authenticate();
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['pet_id'])) {
                http_response_code(400);
                return ['error' => 'Missing pet_id'];
            }

            $adoption = $this->adoptionModel->create(
                $user['id'],
                $data['pet_id'],
                $data['message'] ?? ''
            );

            http_response_code(201);
            return $adoption;
        } catch (Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }

    public function getByUser() {
        try {
            $user = AuthMiddleware::authenticate();
            $adoptions = $this->adoptionModel->getByUser($user['id']);

            return $adoptions;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function getAll() {
        try {
            AuthMiddleware::requireAdmin();

            $filters = [];
            if (isset($_GET['status'])) $filters['status'] = $_GET['status'];

            $adoptions = $this->adoptionModel->getAll($filters);

            return ['data' => $adoptions];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function updateStatus($id) {
        try {
            AuthMiddleware::requireAdmin();

            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['status'])) {
                http_response_code(400);
                return ['error' => 'Missing status'];
            }

            $this->adoptionModel->updateStatus($id, $data['status']);

            return $this->adoptionModel->getById($id);
        } catch (Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }

    public function delete($id) {
        try {
            $user = AuthMiddleware::authenticate();

            // User can only delete their own requests
            $adoption = $this->adoptionModel->getById($id);
            if (!$adoption || $adoption['user_id'] !== $user['id']) {
                http_response_code(403);
                return ['error' => 'Unauthorized'];
            }

            $this->adoptionModel->delete($id);

            return ['message' => 'Adoption request deleted'];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
}
