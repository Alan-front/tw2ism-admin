<?php
require_once 'config.php';
try {
    $id = $_GET['id'] ?? null;
    if (!$id) throw new Exception('ID requerido');
    
    $stmt = $pdo->prepare("DELETE FROM slides WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}