<?php
require_once 'config.php';

try {
    $slide_id = $_POST['slide_id'] ?? null;
    if (!$slide_id) throw new Exception('slide_id requerido');

    $file = $_FILES['file'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) throw new Exception('Archivo requerido');

    // borrar background viejo
    $stmt = $pdo->prepare("SELECT background FROM slides WHERE id = ?");
    $stmt->execute([$slide_id]);
    $slide = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($slide && $slide['background']) {
        $old = __DIR__ . '/uploads/' . $slide['background'];
        if (file_exists($old)) unlink($old);
    }

    // guardar nuevo
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dest = 'C:/xampp/htdocs/tw2ism-admin/uploads/media_scroll/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new Exception('Error al mover archivo');

    $stmt = $pdo->prepare("UPDATE slides SET background = ? WHERE id = ?");
    $stmt->execute([$filename, $slide_id]);

    echo json_encode(['success' => true, 'filename' => $filename]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}