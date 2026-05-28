<?php

// Enable CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load configuration
require_once __DIR__ . '/config/config.php';

// Load classes
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/JWT.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Pet.php';
require_once __DIR__ . '/classes/Adoption.php';
require_once __DIR__ . '/classes/Donation.php';
require_once __DIR__ . '/classes/ReportedAnimal.php';

// Load middleware
require_once __DIR__ . '/middleware/AuthMiddleware.php';

// Load controllers
require_once __DIR__ . '/controllers/UsersController.php';
require_once __DIR__ . '/controllers/PetsController.php';
require_once __DIR__ . '/controllers/AdoptionsController.php';
require_once __DIR__ . '/controllers/DonationsController.php';
require_once __DIR__ . '/controllers/ReportedAnimalsController.php';

// Start session
session_start();

// Get request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Get the route from query parameter or path
$route = null;

// First, try to get from query parameter (?route=...)
if (isset($_GET['route'])) {
    $route = $_GET['route'];
} else {
    // Otherwise, parse from URL path
    $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Remove base paths
    $base_paths = [
        '/PawMatchV2/backend/index.php',
        '/PawMatchV2/backend',
        '/backend/index.php',
        '/backend'
    ];

    foreach ($base_paths as $base) {
        if (strpos($request_path, $base) === 0) {
            $route = substr($request_path, strlen($base));
            break;
        }
    }
}

// Clean the route
$route = trim($route, '/');

// Parse the route into parts
$parts = array_filter(explode('/', $route));
$parts = array_values($parts); // Re-index

// Helper function
function getPart($parts, $index) {
    return isset($parts[$index]) ? $parts[$index] : null;
}

// Get parts safely
$p0 = getPart($parts, 0);
$p1 = getPart($parts, 1);
$p2 = getPart($parts, 2);

$response = null;

try {
    // Route handling
    if ($request_method === 'POST') {
        if ($p0 === 'users' && $p1 === 'register') {
            $controller = new UsersController();
            $response = $controller->register();
        } elseif ($p0 === 'users' && $p1 === 'login') {
            $controller = new UsersController();
            $response = $controller->login();
        } elseif ($p0 === 'users' && $p1 === 'profile' && $p2 === 'update') {
            $controller = new UsersController();
            $response = $controller->updateProfile();
        } elseif ($p0 === 'users' && $p1 === 'change-password') {
            $controller = new UsersController();
            $response = $controller->changePassword();
        } elseif ($p0 === 'pets') {
            $controller = new PetsController();
            $response = $controller->create();
        } elseif ($p0 === 'adoptions') {
            $controller = new AdoptionsController();
            $response = $controller->create();
        } elseif ($p0 === 'donations') {
            $controller = new DonationsController();
            $response = $controller->create();
        } elseif ($p0 === 'reported-animals') {
            $controller = new ReportedAnimalsController();
            $response = $controller->create();
        } elseif ($p0 === 'reported-animals' && $p1 !== null && $p2 === 'update') {
            $controller = new ReportedAnimalsController();
            $response = $controller->addUpdate($p1);
        } else {
            http_response_code(404);
            $response = ['error' => 'Route not found: POST /' . $route];
        }

    } elseif ($request_method === 'GET') {
        if ($p0 === 'users' && $p1 === 'profile') {
            $controller = new UsersController();
            $response = $controller->getProfile();
        } elseif ($p0 === 'pets') {
            if ($p1 !== null) {
                // Get specific pet
                $controller = new PetsController();
                $response = $controller->getById($p1);
            } else {
                // Get all pets
                $controller = new PetsController();
                $response = $controller->getAll();
            }
        } elseif ($p0 === 'adoptions') {
            if ($p1 === 'all') {
                $controller = new AdoptionsController();
                $response = $controller->getAll();
            } else {
                $controller = new AdoptionsController();
                $response = $controller->getByUser();
            }
        } elseif ($p0 === 'donations') {
            if ($p1 === 'stats') {
                $controller = new DonationsController();
                $response = $controller->getStats();
            } elseif ($p1 === 'user' && $p2 === 'my-donations') {
                $controller = new DonationsController();
                $response = $controller->getByUser();
            } else {
                $controller = new DonationsController();
                $response = $controller->getAll();
            }
        } elseif ($p0 === 'reported-animals') {
            if ($p1 === 'nearby') {
                $controller = new ReportedAnimalsController();
                $response = $controller->getNearby();
            } elseif ($p1 !== null) {
                $controller = new ReportedAnimalsController();
                $response = $controller->getById($p1);
            } else {
                $controller = new ReportedAnimalsController();
                $response = $controller->getAll();
            }
        } else {
            http_response_code(404);
            $response = ['error' => 'Route not found: GET /' . $route];
        }

    } elseif ($request_method === 'PUT') {
        if ($p0 === 'pets' && $p1 !== null) {
            $controller = new PetsController();
            $response = $controller->update($p1);
        } elseif ($p0 === 'adoptions' && $p1 !== null) {
            $controller = new AdoptionsController();
            $response = $controller->updateStatus($p1);
        } elseif ($p0 === 'reported-animals' && $p1 !== null && $p2 === 'edit') {
            $controller = new ReportedAnimalsController();
            $response = $controller->update($p1);
        } elseif ($p0 === 'reported-animals' && $p1 !== null) {
            $controller = new ReportedAnimalsController();
            $response = $controller->updateStatus($p1);
        } else {
            http_response_code(404);
            $response = ['error' => 'Route not found: PUT /' . $route];
        }

    } elseif ($request_method === 'DELETE') {
        if ($p0 === 'pets' && $p1 !== null) {
            $controller = new PetsController();
            $response = $controller->delete($p1);
        } elseif ($p0 === 'adoptions' && $p1 !== null) {
            $controller = new AdoptionsController();
            $response = $controller->delete($p1);
        } elseif ($p0 === 'reported-animals' && $p1 !== null) {
            $controller = new ReportedAnimalsController();
            $response = $controller->delete($p1);
        } else {
            http_response_code(404);
            $response = ['error' => 'Route not found: DELETE /' . $route];
        }

    } else {
        http_response_code(405);
        $response = ['error' => 'Method not allowed'];
    }

} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => $e->getMessage()];
}

// Send response
echo json_encode($response);
