<?php

class PetsController {
    private $petModel;

    public function __construct() {
        $this->petModel = new Pet();
    }

    public function getAll() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 10;

            $filters = [];
            if (isset($_GET['species'])) $filters['species'] = $_GET['species'];
            if (isset($_GET['size'])) $filters['size'] = $_GET['size'];
            if (isset($_GET['energy'])) $filters['energy'] = $_GET['energy'];
            if (isset($_GET['search'])) $filters['search'] = $_GET['search'];

            $result = $this->petModel->getAll($filters, $page, $limit);

            return $result;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function getById($id) {
        try {
            $pet = $this->petModel->getById($id);

            if (!$pet) {
                http_response_code(404);
                return ['error' => 'Pet not found'];
            }

            return $pet;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function create() {
        try {
            AuthMiddleware::requireAdmin();

            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['name']) || !isset($data['species']) || !isset($data['breed'])) {
                http_response_code(400);
                return ['error' => 'Missing required fields'];
            }

            $pet = $this->petModel->create($data);
            http_response_code(201);
            return $pet;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function update($id) {
        try {
            AuthMiddleware::requireAdmin();

            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                http_response_code(400);
                return ['error' => 'Invalid data'];
            }

            if (!$this->petModel->update($id, $data)) {
                http_response_code(400);
                return ['error' => 'Failed to update pet'];
            }

            return $this->petModel->getById($id);
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function delete($id) {
        try {
            AuthMiddleware::requireAdmin();

            if (!$this->petModel->delete($id)) {
                http_response_code(400);
                return ['error' => 'Failed to delete pet'];
            }

            return ['message' => 'Pet deleted successfully'];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
}
