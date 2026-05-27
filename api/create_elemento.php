<?php
require_once 'config.php';

try {
    if (!isset($_FILES['file'])) throw new Exception('Archivo requerido');

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','mp4','webm','mov','svg'];
    if (!in_array($ext, $allowed)) throw new Exception('Tipo no permitido');

    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dest = 'C:/xampp/htdocs/tw2ism-admin/uploads/media_scroll/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new Exception('Error al mover archivo');

    if (in_array($ext, ['mp4','webm','mov'])) $type = 'video';
    elseif ($ext === 'gif') $type = 'gif';
    else $type = 'image';

    $stmt = $pdo->prepare("
        INSERT INTO slide_elementos 
        (slide_id, filename, type, pos_x, pos_y, width, rotation, z_index, title, description, url, sound_enabled, posicion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'custom')
    ");
    $stmt->execute([
        $_POST['slide_id'],
        $filename,
        $type,
        $_POST['pos_x'] ?? 25,
        $_POST['pos_y'] ?? 25,
        $_POST['width'] ?? 40,
        $_POST['rotation'] ?? 0,
        $_POST['z_index'] ?? 0,
        $_POST['title'] ?? '',
        $_POST['description'] ?? '',
        $_POST['url'] ?? '',
        isset($_POST['sound_enabled']) ? (int)$_POST['sound_enabled'] : 0
    ]);

    $id = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'elemento' => [
        'id'           => (int)$id,
        'filename'     => $filename,
        'type'         => $type,
        'pos_x'        => (float)($_POST['pos_x'] ?? 25),
        'pos_y'        => (float)($_POST['pos_y'] ?? 25),
        'width'        => (float)($_POST['width'] ?? 40),
        'rotation'     => (float)($_POST['rotation'] ?? 0),
        'z_index'      => (int)($_POST['z_index'] ?? 0),
        'title'        => $_POST['title'] ?? '',
        'description'  => $_POST['description'] ?? '',
        'url'          => $_POST['url'] ?? '',
        'sound_enabled'=> (bool)(int)($_POST['sound_enabled'] ?? 0),
        'posicion'     => 'custom'
    ]]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}