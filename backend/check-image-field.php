<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "=== Estructura de tabla reported_animals ===\n\n";

    $columns = $pdo->query('DESCRIBE reported_animals')->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo "Campo: {$col['Field']}\n";
        echo "Tipo: {$col['Type']}\n";
        echo "Null: {$col['Null']}\n";
        echo "---\n";
    }

    echo "\n=== Información de imágenes guardadas ===\n\n";

    $images = $pdo->query('SELECT id, type, LENGTH(image) as image_size FROM reported_animals WHERE image IS NOT NULL AND image != ""')->fetchAll(PDO::FETCH_ASSOC);

    if (count($images) > 0) {
        echo "Reportes con imágenes: " . count($images) . "\n\n";
        foreach ($images as $report) {
            $sizeKB = round($report['image_size'] / 1024, 2);
            echo "ID: {$report['id']}\n";
            echo "Tipo: {$report['type']}\n";
            echo "Tamaño imagen: {$sizeKB} KB\n";
            echo "---\n";
        }
    } else {
        echo "No hay reportes con imágenes guardadas aún.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
