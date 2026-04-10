<?php
require_once 'config.php';
$data = json_decode(file_get_contents('php://input'), true);
try {
    $stmt = $pdo->prepare("
        UPDATE slide_elementos
        SET title = ?, description = ?, url = ?,
            pos_x = ?, pos_y = ?, width = ?,
            rotation = ?, z_index = ?, sound_enabled = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['title'],
        $data['description'],
        $data['url'],
        $data['pos_x'],
        $data['pos_y'],
        $data['width'],
        $data['rotation'],
        $data['z_index'],
        $data['sound_enabled'] ? 1 : 0,
        $data['id']
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}