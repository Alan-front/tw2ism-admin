<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $audio_url = $_POST['audio_url'] ?? '';
    
    if (empty($audio_url)) {
        echo json_encode(['success' => false, 'message' => 'URL no puede estar vacía']);
        exit;
    }
    
    try {
        // usar REPLACE para insertar o actualizar automáticamente
        // REPLACE elimina el registro existente (si existe) e inserta el nuevo
        $stmt = $pdo->prepare("REPLACE INTO audio_config (id, soundcloud_url, active, updated_at) VALUES (1, ?, 1, NOW())");
        $stmt->execute([$audio_url]);
        
        echo json_encode(['success' => true, 'message' => 'Audio guardado correctamente']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>