<?php
require_once 'config.php';

try {
    $id = $_POST['id'] ?? null;
    if (!$id) throw new Exception('ID requerido');

    $file = $_FILES['file'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) throw new Exception('Archivo requerido');

    // borrar archivo viejo
    $stmt = $pdo->prepare("SELECT filename FROM slide_elementos WHERE id = ?");
    $stmt->execute([$id]);
    $el = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($el && $el['filename']) {
        $old = 'C:/xampp/htdocs/tw2ism-admin/uploads/media_scroll/' . $el['filename'];
        if (file_exists($old)) unlink($old);
    }

    // guardar nuevo
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dest = 'C:/xampp/htdocs/tw2ism-admin/uploads/media_scroll/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new Exception('Error al mover archivo');

    if ($ext === 'mp4' || $ext === 'webm' || $ext === 'mov') $type = 'video';
    elseif ($ext === 'gif') $type = 'gif';
    else $type = 'image';

    $stmt = $pdo->prepare("UPDATE slide_elementos SET filename = ?, type = ? WHERE id = ?");
    $stmt->execute([$filename, $type, $id]);

    echo json_encode(['success' => true, 'filename' => $filename, 'type' => $type]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}