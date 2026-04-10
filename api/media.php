<?php
require_once 'config.php';

try {

    $stmt = $pdo->query("
        SELECT 
            s.id as slide_id,
            s.orden as slide_orden,
            s.layout_tipo,
            s.background,
            s.height_vh,

            e.id as elemento_id,
            e.filename,
            e.type,
            e.posicion,
            e.pos_x,
            e.pos_y,
            e.width,
            e.rotation,
            e.z_index,
            e.title,
            e.description,
            e.url,
            e.sound_enabled
            
            

        FROM slides s
        LEFT JOIN slide_elementos e ON e.slide_id = s.id
        WHERE s.active = 1 OR s.active IS NULL
        ORDER BY s.orden ASC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $slides = [];

    foreach ($rows as $row) {

        $slide_id = $row['slide_id'];

        if (!isset($slides[$slide_id])) {
            $slides[$slide_id] = [
                'id'         => $slide_id,
                'orden'      => $row['slide_orden'],
                'layout_tipo'=> $row['layout_tipo'],
                'background' => $row['background'],
                'height_vh'  => (float)$row['height_vh'],
                'elementos'  => []
            ];
        }

        if ($row['elemento_id']) {
            $slides[$slide_id]['elementos'][] = [
                'id'           => $row['elemento_id'],
                'filename'     => $row['filename'],
                'type'         => $row['type'],
                'posicion'     => $row['posicion'],
                'pos_x'        => (float)$row['pos_x'],
                'pos_y'        => (float)$row['pos_y'],
                'width'        => (float)$row['width'],
                'rotation'     => (float)$row['rotation'],
                'z_index'      => (int)$row['z_index'],
                'title'        => $row['title'],
                'description'  => $row['description'],
                'url'          => $row['url'],
                'sound_enabled'=> (bool)$row['sound_enabled'],
                
               
            ];
        }
    }

    $slides = array_values($slides);

    echo json_encode([
        'success' => true,
        'slides'  => $slides
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}