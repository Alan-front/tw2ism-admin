<?php
require_once 'config.php';

if (!isset($_FILES['file']) || !isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Archivo e ID requeridos']);
    exit;
}

$id = $_POST['id'];
$file = $_FILES['file'];

// validar archivo
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Tipo de archivo no válido']);
    exit;
}

try {
    // obtener archivo anterior
    $stmt = $pdo->prepare("SELECT filename FROM media_items WHERE id = ?");
    $stmt->execute([$id]);
    $oldItem = $stmt->fetch();
    
    if (!$oldItem) {
        echo json_encode(['success' => false, 'error' => 'Item no encontrado']);
        exit;
    }
    
    // Eliminar archivo anterior
    if ($oldItem['filename']) {
        $oldPath = '../uploads/media_scroll/' . $oldItem['filename'];
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }
    
    // subir nuevo archivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $uploadPath = '../uploads/media_scroll/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => false, 'error' => 'Error subiendo archivo']);
        exit;
    }
    
    // determinar tipo
    $type = 'image';
    if (strpos($file['type'], 'video') !== false) {
        $type = 'video';
    } elseif (strtolower($extension) === 'gif') {
        $type = 'gif';
    }
    
    // set sound_enabled based on type: 1 for video, 0 for image/gif
    $sound_enabled = ($type === 'video') ? 1 : 0;
    
    // actualizar base de datos con filename, type y sound_enabled
    $stmt = $pdo->prepare("UPDATE media_items SET filename = ?, type = ?, sound_enabled = ? WHERE id = ?");
    $stmt->execute([$filename, $type, $sound_enabled, $id]);
    
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'type' => $type,
        'sound_enabled' => (bool)$sound_enabled
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}