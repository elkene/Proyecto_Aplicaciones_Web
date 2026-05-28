<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Create reported_animals table
    $sql1 = "CREATE TABLE IF NOT EXISTS reported_animals (
        id VARCHAR(36) PRIMARY KEY,
        user_id VARCHAR(36) NOT NULL,
        type VARCHAR(100) NOT NULL,
        description TEXT,
        latitude DECIMAL(10, 8) NOT NULL,
        longitude DECIMAL(11, 8) NOT NULL,
        phone VARCHAR(20),
        image VARCHAR(255),
        status ENUM('pending', 'in_rescue', 'rescued', 'unknown') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_status (status),
        INDEX idx_location (latitude, longitude),
        INDEX idx_date (created_at)
    )";

    $pdo->exec($sql1);
    echo "✓ Table 'reported_animals' created\n";

    // Create animal_report_updates table
    $sql2 = "CREATE TABLE IF NOT EXISTS animal_report_updates (
        id VARCHAR(36) PRIMARY KEY,
        report_id VARCHAR(36) NOT NULL,
        user_id VARCHAR(36) NOT NULL,
        update_type ENUM('comment', 'status_change', 'location_update') DEFAULT 'comment',
        content TEXT NOT NULL,
        new_status VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (report_id) REFERENCES reported_animals(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_report (report_id),
        INDEX idx_user (user_id)
    )";

    $pdo->exec($sql2);
    echo "✓ Table 'animal_report_updates' created\n";

    // Check if sample data already exists
    $check = $pdo->query("SELECT COUNT(*) as count FROM reported_animals")->fetch(PDO::FETCH_ASSOC);

    if ($check['count'] == 0) {
        // Insert sample data
        $sql3 = "INSERT INTO reported_animals (id, user_id, type, description, latitude, longitude, phone, image, status, notes, created_at, updated_at) VALUES
        ('660e8400-e29b-41d4-a716-446655440201', '550e8400-e29b-41d4-a716-446655440002', 'Perro', 'Perro café con collar azul, muy asustado', 32.5149, -116.9718, '6641234567', 'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400&h=300&fit=crop', 'pending', 'Visto cerca del parque central', NOW(), NOW()),
        ('660e8400-e29b-41d4-a716-446655440202', '550e8400-e29b-41d4-a716-446655440002', 'Gato', 'Gato gris y blanco, herido en una pata', 32.5150, -116.9720, '6649876543', 'https://images.unsplash.com/photo-1513360371669-4adf3dd7dff8?w=400&h=300&fit=crop', 'in_rescue', 'Ya ha sido recogido por voluntarios', NOW(), NOW())";

        $pdo->exec($sql3);
        echo "✓ Sample data inserted\n";
    } else {
        echo "✓ Sample data already exists\n";
    }

    echo "\n✓ Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
