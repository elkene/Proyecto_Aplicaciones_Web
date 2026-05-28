<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "Modificando campo 'image' a LONGTEXT...\n";

    $sql = "ALTER TABLE reported_animals MODIFY COLUMN image LONGTEXT NULL";
    $pdo->exec($sql);

    echo "✓ Campo 'image' modificado a LONGTEXT\n";
    echo "✓ Ahora puede almacenar imágenes base64 de hasta 4GB\n";

    // Verify
    $columns = $pdo->query('DESCRIBE reported_animals')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        if ($col['Field'] == 'image') {
            echo "\nVerificación:\n";
            echo "Campo: {$col['Field']}\n";
            echo "Tipo: {$col['Type']}\n";
        }
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
    exit(1);
}
