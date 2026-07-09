<?php
require_once 'config.php';

try {
    $slide_id = $_POST['slide_id'] ?? null;
    if (!$slide_id) throw new Exception('slide_id requerido');

    // 1. Obtener background actual SIEMPRE
    $stmt = $pdo->prepare("SELECT background FROM slides WHERE id = ?");
    $stmt->execute([$slide_id]);
    $slide = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($slide && !empty($slide['background'])) {
        $old = $uploadsPath . trim($slide['background']);
        if (file_exists($old)) {

        
            unlink($old);
        }
    }

    // 2. if no file = delete background
    if (empty($_FILES['file'])) {
        $stmt = $pdo->prepare("UPDATE slides SET background = '' WHERE id = ?");
        $stmt->execute([$slide_id]);

        echo json_encode(['success' => true, 'deleted' => true]);
        exit;
    }

    // 3. Upload normal (reemplazo)
    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception('Error en archivo');

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dest = $uploadsPath . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new Exception('Error al mover archivo');
    }

    $stmt = $pdo->prepare("UPDATE slides SET background = ? WHERE id = ?");
    $stmt->execute([$filename, $slide_id]);

    echo json_encode(['success' => true, 'filename' => $filename]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}