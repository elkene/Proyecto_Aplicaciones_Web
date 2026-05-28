<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "✓ Database connection successful\n\n";

    // Check tables
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        echo "  ✓ $table\n";
    }

    // Check reported_animals table structure
    echo "\nReported Animals Table Structure:\n";
    $columns = $pdo->query('DESCRIBE reported_animals')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }

    // Check sample data
    $count = $pdo->query('SELECT COUNT(*) FROM reported_animals')->fetch(PDO::FETCH_COLUMN);
    echo "\n✓ Sample data: $count reports in database\n";

    // List sample reports
    $reports = $pdo->query('SELECT id, type, status, reporter_name FROM (SELECT r.*, u.name as reporter_name FROM reported_animals r JOIN users u ON r.user_id = u.id) as t')->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample Reports:\n";
    foreach ($reports as $report) {
        echo "  - {$report['type']} (ID: {$report['id']}, Status: {$report['status']}, Reporter: {$report['reporter_name']})\n";
    }

    echo "\n✓ Setup verification complete!\n";
    echo "\nYou can now:\n";
    echo "  1. Login to the application at http://localhost/PawMatchV2/public/\n";
    echo "  2. Navigate to the '🗺️ Reportes' section to see and create animal reports\n";
    echo "  3. Click on the map to select a report location\n";
    echo "  4. Submit a new report and view it on the map\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
