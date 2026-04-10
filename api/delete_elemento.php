<?php
require_once 'config.php';
try {
    $id = $_GET['id'] ?? null;
    if (!$id) throw new Exception('ID requerido');

    $stmt = $pdo->prepare("SELECT filename FROM slide_elementos WHERE id = ?");
    $stmt->execute([$id]);
    $el = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($el && $el['filename']) {
        $file = 'C:/xampp/htdocs/tw2ism-admin/uploads/media_scroll/' . $filename;
        if (file_exists($file)) unlink($file);
    }

    $stmt = $pdo->prepare("DELETE FROM slide_elementos WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}