<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$isLocal = $_SERVER['HTTP_HOST'] === 'localhost';

$host = $isLocal ? 'localhost' : 'sql206.infinityfree.com';
$db   = $isLocal ? 'tw2ism_db' : 'if0_42361720_datostw2';
$user = $isLocal ? 'root' : 'if0_42361720';
$pass = $isLocal ? '' : '1fr1ErnOlpgGO';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$uploadsPath = $isLocal
    ? 'C:/xampp/htdocs/tw2ism-admin/uploads/media_scroll/'
    : $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
?>