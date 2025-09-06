<?php
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE media_items 
        SET title = ?, description = ?, link = ?, sound_enabled = ?, active = ? 
        WHERE id = ?
    ");
    
    $stmt->execute([
        $input['title'] ?? '',
        $input['description'] ?? '',
        $input['link'] ?? '',
        $input['sound_enabled'] ? 1 : 0,
        $input['active'] ? 1 : 0,
        $input['id']
    ]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}