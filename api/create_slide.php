<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT MAX(orden) as max_orden FROM slides");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nuevo_orden = ($row['max_orden'] ?? 0) + 1;

    $stmt = $pdo->prepare("INSERT INTO slides (orden, layout_tipo, background, height_vh) VALUES (?, '', NULL, 100)");
    $stmt->execute([$nuevo_orden]);
    $id = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'slide' => [
        'id'          => (int)$id,
        'orden'       => (int)$nuevo_orden,
        'layout_tipo' => '',
        'background'  => null,
        'height_vh'   => 100.0,
        'elementos'   => []
    ]]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}