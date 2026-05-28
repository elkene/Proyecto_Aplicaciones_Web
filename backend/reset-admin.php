<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Reset admin password to Admin@123
    $email = 'admin@pawmatch.com';
    $newPassword = 'Admin@123';
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$hashedPassword, $email])) {
        echo "✓ Admin password reset successfully!\n";
        echo "Email: $email\n";
        echo "Password: $newPassword\n";
    } else {
        echo "✗ Error updating password\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
