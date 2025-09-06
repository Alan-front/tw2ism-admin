<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM media_items ORDER BY created_at DESC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // convertir valores booleanos
    foreach ($items as &$item) {
        $item['sound_enabled'] = (bool)$item['sound_enabled'];
        $item['active'] = (bool)$item['active'];
    }
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}