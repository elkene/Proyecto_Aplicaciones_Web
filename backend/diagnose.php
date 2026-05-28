<?php
header('Content-Type: application/json; charset=utf-8');

$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'extensions' => [],
    'config' => [],
    'database' => [],
    'errors' => []
];

// Check extensions
$extensions = ['PDO', 'pdo_mysql', 'json', 'curl'];
foreach ($extensions as $ext) {
    $diagnostics['extensions'][$ext] = extension_loaded($ext);
}

// Check config file
try {
    require_once __DIR__ . '/config/config.php';

    $diagnostics['config'] = [
        'DB_HOST' => DB_HOST,
        'DB_PORT' => DB_PORT,
        'DB_NAME' => DB_NAME,
        'DB_USER' => DB_USER,
        'DEBUG' => DEBUG
    ];

    // Try to connect
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_USER_PASS ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $diagnostics['database']['connection'] = 'OK';

        // Check tables
        $stmt = $pdo->query("SHOW TABLES FROM " . DB_NAME);
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $diagnostics['database']['tables'] = $tables;

    } catch (PDOException $e) {
        $diagnostics['database']['connection'] = 'FAILED';
        $diagnostics['database']['error'] = $e->getMessage();
    }

} catch (Exception $e) {
    $diagnostics['errors'][] = "Config Error: " . $e->getMessage();
}

// Check critical files
$files = [
    'index.php',
    'config/config.php',
    'classes/Database.php',
    'classes/User.php',
    'controllers/UsersController.php'
];

$diagnostics['files'] = [];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $diagnostics['files'][$file] = file_exists($path);
}

http_response_code(200);
echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
