<?php
require_once 'config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $orden = $data['orden'] ?? [];
    if (empty($orden)) throw new Exception('Orden vacío');

    $stmt = $pdo->prepare("UPDATE slides SET orden = ? WHERE id = ?");
    foreach ($orden as $index => $id) {
        $stmt->execute([$index + 1, $id]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}