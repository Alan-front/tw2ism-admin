<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['orden'])) {
    http_response_code(400);
    echo json_encode(['success' => false]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE media_items SET sort_order = ? WHERE id = ?");
    foreach ($data['orden'] as $pos => $id) {
        $stmt->execute([$pos + 1, (int)$id]);
    }
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}