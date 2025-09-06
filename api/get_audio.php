<?php
require_once 'config.php';

try {
     // obtener el audio más reciente activo
    $stmt = $pdo->prepare("SELECT soundcloud_url FROM audio_config WHERE active = 1 ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute();
    $audio = $stmt->fetch();
    
    if ($audio) {
        echo json_encode(['success' => true, 'audio_url' => $audio['soundcloud_url']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No hay audio disponible']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>