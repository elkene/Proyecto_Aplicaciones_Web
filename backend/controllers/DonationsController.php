<?php

class DonationsController {
    private $donationModel;

    public function __construct() {
        $this->donationModel = new Donation();
    }

    public function create() {
        try {
            $user = AuthMiddleware::authenticate();
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['amount'])) {
                http_response_code(400);
                return ['error' => 'Missing amount'];
            }

            $amount = (float)$data['amount'];
            if ($amount <= 0) {
                http_response_code(400);
                return ['error' => 'Amount must be greater than 0'];
            }

            $donation = $this->donationModel->create(
                $user['id'],
                $amount,
                $data['currency'] ?? 'MXN',
                $data['message'] ?? ''
            );

            http_response_code(201);
            return $donation;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function getByUser() {
        try {
            $user = AuthMiddleware::authenticate();
            $donations = $this->donationModel->getByUser($user['id']);

            return $donations;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function getAll() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 10;

            $result = $this->donationModel->getAll($page, $limit);

            return $result;
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }

    public function getStats() {
        try {
            $stats = $this->donationModel->getStats();

            return [
                'totalDonations' => (int)$stats['totalDonations'],
                'totalAmount' => (float)$stats['totalAmount']
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
}
