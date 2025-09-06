<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$uploadDir = '../uploads/media_scroll/';

// crear directorio si no existe
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// validar archivo
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/ogg'];
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'File type not allowed']);
    exit;
}

// generaar nombre de archivo unico
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// mover archivo subido
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Determine file type
    $type = 'image';
    if (strpos($file['type'], 'video') === 0) {
        $type = 'video';
    } elseif ($extension === 'gif') {
        $type = 'gif';
    }
    
    // guardar con valor 1 si es video
    $sound_enabled = ($type === 'video') ? 1 : 0;
    
    // guardar en tabla
    try {
        $stmt = $pdo->prepare("
            INSERT INTO media_items (filename, title, description, link, sound_enabled, type, layout_class, active) 
            VALUES (?, '', '', '', ?, ?, '', 1)
        ");
        $stmt->execute([$filename, $sound_enabled, $type]);
        
        $itemId = $pdo->lastInsertId();
        
     // retorna item creado
        $stmt = $pdo->prepare("SELECT * FROM media_items WHERE id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();
        
          // convert boolean values for frontend
        $item['sound_enabled'] = (bool)$item['sound_enabled'];
        $item['active'] = (bool)$item['active'];
        
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully',
            'item' => $item
        ]);
        
    } catch (PDOException $e) {
        // delete uploaded file if database insert fails
        unlink($filepath);
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to upload file']);
}
?>