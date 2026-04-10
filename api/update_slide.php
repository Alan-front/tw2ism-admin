<?php
require_once 'config.php';
$data = json_decode(file_get_contents('php://input'), true);
try {
    $stmt = $pdo->prepare("
        UPDATE slides 
        SET background = ?, height_vh = ?, layout_tipo = ?, orden = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['background'],
        $data['height_vh'],
        $data['layout_tipo'] ?? '',
        $data['orden'],
        $data['id']
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}