<?php
header('Content-Type: application/json');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Generate hash for "Admin@123"
    $password = 'Admin@123';
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Update admin user
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = 'admin@pawmatch.com'");
    $result = $stmt->execute([$hash]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully',
            'email' => 'admin@pawmatch.com',
            'password' => 'Admin@123',
            'hash' => $hash
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update password'
        ]);
    }

    // Also check what users exist
    $stmt = $db->prepare("SELECT id, email, name FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'message' => 'Password reset successfully',
        'users_in_database' => $users
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
