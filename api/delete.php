<?php
require_once 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID requerido']);
    exit;
}

try {
    // btener filename antes de eliminar
    $stmt = $pdo->prepare("SELECT filename FROM media_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Item no encontrado']);
        exit;
    }
    
    // Eliminar archivo físico
    $filepath = '../uploads/media_scroll/' . $item['filename'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    
    // eliminar de base de datos
    $stmt = $pdo->prepare("DELETE FROM media_items WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}