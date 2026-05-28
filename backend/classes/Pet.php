<?php

class Pet {
    private $db;
    private $table = 'pets';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($filters = [], $page = 1, $limit = 10) {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['species'])) {
                $where[] = "species = ?";
                $params[] = $filters['species'];
            }
            if (!empty($filters['size'])) {
                $where[] = "size = ?";
                $params[] = $filters['size'];
            }
            if (!empty($filters['energy'])) {
                $where[] = "energy = ?";
                $params[] = $filters['energy'];
            }
            if (!empty($filters['search'])) {
                $where[] = "MATCH(name, breed, description) AGAINST(? IN BOOLEAN MODE)";
                $params[] = $filters['search'];
            }

            $whereClause = empty($where) ? '1=1' : implode(' AND ', $where);
            $offset = (int)(($page - 1) * $limit);
            $limit = (int)$limit;

            // Build query with LIMIT as string (not parameter)
            $sql = "SELECT * FROM {$this->table} WHERE $whereClause LIMIT $offset, $limit";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $pets = $stmt->fetchAll();

            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $countResult = $countStmt->fetch();
            $total = $countResult['total'];

            // Process images
            foreach ($pets as &$pet) {
                if ($pet['images']) {
                    $pet['images'] = json_decode($pet['images'], true);
                }
                if ($pet['compatibility']) {
                    $pet['compatibility'] = json_decode($pet['compatibility'], true);
                }
                $pet['medical'] = [
                    'vaccinated' => (bool)$pet['vaccinated'],
                    'sterilized' => (bool)$pet['sterilized'],
                    'microchip' => (bool)$pet['microchip']
                ];
            }

            return [
                'data' => $pets,
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
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $pet = $stmt->fetch();

            if ($pet) {
                if ($pet['images']) {
                    $pet['images'] = json_decode($pet['images'], true);
                }
                if ($pet['compatibility']) {
                    $pet['compatibility'] = json_decode($pet['compatibility'], true);
                }
                $pet['medical'] = [
                    'vaccinated' => (bool)$pet['vaccinated'],
                    'sterilized' => (bool)$pet['sterilized'],
                    'microchip' => (bool)$pet['microchip']
                ];
            }

            return $pet;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function create($data) {
        try {
            $id = $this->generateUUID();

            $stmt = $this->db->prepare("
                INSERT INTO {$this->table}
                (id, name, species, breed, age, size, energy, location, image, images, description, compatibility, vaccinated, sterilized, microchip, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $images = json_encode($data['images'] ?? []);
            $compatibility = json_encode($data['compatibility'] ?? []);

            $params = [
                $id,
                $data['name'],
                $data['species'],
                $data['breed'],
                $data['age'],
                $data['size'],
                $data['energy'],
                $data['location'],
                $data['image'] ?? null,
                $images,
                $data['description'],
                $compatibility,
                $data['medical']['vaccinated'] ?? false,
                $data['medical']['sterilized'] ?? false,
                $data['medical']['microchip'] ?? false
            ];

            if (!$stmt->execute($params)) {
                throw new Exception("Failed to create pet");
            }

            return $this->getById($id);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function update($id, $data) {
        try {
            $fields = [];
            $values = [];
            $allowedFields = ['name', 'species', 'breed', 'age', 'size', 'energy', 'location', 'image', 'description', 'vaccinated', 'sterilized', 'microchip'];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
            }

            if (isset($data['images'])) {
                $fields[] = "images = ?";
                $values[] = json_encode($data['images']);
            }

            if (isset($data['compatibility'])) {
                $fields[] = "compatibility = ?";
                $values[] = json_encode($data['compatibility']);
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
