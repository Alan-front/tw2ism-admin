<?php
require_once 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID requerido']);
    exit;
}

try {
     // obtener archivo actual
    $stmt = $pdo->prepare("SELECT filename FROM media_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo json_encode(['success' => false, 'error' => 'Item no encontrado']);
        exit;
    }
    
    // eliminar archivo fsico si existe
    if ($item['filename']) {
        $filepath = '../uploads/media_scroll/' . $item['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
    
    // actualizar BD - solo quitar filename, mantener metadatos
    $stmt = $pdo->prepare("UPDATE media_items SET filename = '' WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}