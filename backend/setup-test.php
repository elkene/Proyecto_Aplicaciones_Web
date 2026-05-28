<?php

header('Content-Type: text/html; charset=utf-8');

$results = [
    'php' => phpversion(),
    'database' => false,
    'files' => [],
    'errors' => []
];

// Check PHP version
if (version_compare(phpversion(), '7.4.0', '<')) {
    $results['errors'][] = 'PHP version is too old (required 7.4+)';
} else {
    $results['php_ok'] = true;
}

// Check required files
$files = [
    'config/config.php',
    'classes/Database.php',
    'classes/JWT.php',
    'classes/User.php',
    'classes/Pet.php',
    'classes/Adoption.php',
    'classes/Donation.php',
    'middleware/AuthMiddleware.php',
    'controllers/UsersController.php',
    'controllers/PetsController.php',
    'controllers/AdoptionsController.php',
    'controllers/DonationsController.php',
    'index.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $results['files'][$file] = file_exists($path);
    if (!file_exists($path)) {
        $results['errors'][] = "Missing file: $file";
    }
}

// Check database connection
try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/classes/Database.php';

    $db = Database::getInstance();
    $connection = $db->getConnection();

    if ($connection) {
        $results['database'] = true;

        // Check if tables exist
        $stmt = $connection->query("SHOW TABLES FROM " . DB_NAME);
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $required_tables = ['users', 'pets', 'adoption_requests', 'donations'];
        $results['tables'] = [];

        foreach ($required_tables as $table) {
            $results['tables'][$table] = in_array($table, $tables);
        }

        if (count($tables) === 0) {
            $results['errors'][] = 'Database is empty - please import schema.sql';
        }
    }
} catch (Exception $e) {
    $results['database'] = false;
    $results['errors'][] = 'Database Error: ' . $e->getMessage();
}

// Check if PDO is available
if (!extension_loaded('pdo_mysql')) {
    $results['errors'][] = 'PDO MySQL extension is not installed';
}

// Generate HTML report
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawMatch - Setup Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #FF6B35;
            text-align: center;
        }
        .section {
            margin: 20px 0;
        }
        .section h2 {
            color: #333;
            border-bottom: 2px solid #FF6B35;
            padding-bottom: 10px;
        }
        .status {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .status-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 15px;
            font-size: 16px;
        }
        .status-icon.ok {
            background: #4CAF50;
        }
        .status-icon.error {
            background: #F44336;
        }
        .status-text {
            flex: 1;
        }
        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .next-steps {
            background: #e3f2fd;
            padding: 20px;
            border-left: 4px solid #2196F3;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐾 PawMatch - Setup Test Report</h1>

        <?php if (count($results['errors']) > 0): ?>
            <div class="alert alert-error">
                <strong>⚠️ Issues Found:</strong>
                <ul>
                    <?php foreach ($results['errors'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                ✅ All checks passed! Your PawMatch installation is ready.
            </div>
        <?php endif; ?>

        <div class="section">
            <h2>🔧 System Information</h2>
            <div class="status">
                <div class="status-icon <?php echo isset($results['php_ok']) ? 'ok' : 'error'; ?>">
                    <?php echo isset($results['php_ok']) ? '✓' : '✗'; ?>
                </div>
                <div class="status-text">
                    PHP Version: <strong><?php echo $results['php']; ?></strong>
                    (Required: 7.4+)
                </div>
            </div>
        </div>

        <div class="section">
            <h2>📁 Required Files</h2>
            <table>
                <tr>
                    <th>File</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($results['files'] as $file => $exists): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($file); ?></td>
                        <td>
                            <span class="status-icon <?php echo $exists ? 'ok' : 'error'; ?>" style="width: 20px; height: 20px; font-size: 12px;">
                                <?php echo $exists ? '✓' : '✗'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="section">
            <h2>🗄️ Database Connection</h2>
            <div class="status">
                <div class="status-icon <?php echo $results['database'] ? 'ok' : 'error'; ?>">
                    <?php echo $results['database'] ? '✓' : '✗'; ?>
                </div>
                <div class="status-text">
                    Database Connection: <strong><?php echo $results['database'] ? 'Connected' : 'Failed'; ?></strong>
                </div>
            </div>

            <?php if ($results['database'] && isset($results['tables'])): ?>
                <h3>Database Tables</h3>
                <table>
                    <tr>
                        <th>Table</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach ($results['tables'] as $table => $exists): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($table); ?></td>
                            <td>
                                <span class="status-icon <?php echo $exists ? 'ok' : 'error'; ?>" style="width: 20px; height: 20px; font-size: 12px;">
                                    <?php echo $exists ? '✓' : '✗'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <div class="next-steps">
            <h3>📝 Next Steps</h3>
            <p>If all checks passed, you can:</p>
            <ol>
                <li>Access the application: <code>http://localhost/PawMatchV2/public/</code></li>
                <li>Log in with demo credentials:
                    <ul>
                        <li>Email: <code>admin@pawmatch.com</code></li>
                        <li>Password: <code>Admin@123</code></li>
                    </ul>
                </li>
                <li>Start exploring PawMatch! 🐾</li>
            </ol>

            <p>If there are errors:</p>
            <ol>
                <li>Check the errors listed above</li>
                <li>Refer to <code>INSTALLATION.md</code> for detailed troubleshooting</li>
                <li>Ensure XAMPP is running (Apache + MySQL)</li>
                <li>Verify the database was imported correctly</li>
            </ol>
        </div>

        <div class="footer">
            <p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>PawMatch v2 - 2024</p>
        </div>
    </div>
</body>
</html>
