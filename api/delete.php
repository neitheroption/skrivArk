<?php
header('Content-Type: application/json; charset=utf-8');

$body     = json_decode(file_get_contents('php://input'), true);
$filename = basename($body['filename'] ?? '');

if (!$filename) {
    http_response_code(400);
    echo json_encode(['error' => 'Filnamn saknas']);
    exit;
}

$path = dirname(__DIR__) . '/images/' . $filename;

if (!file_exists($path)) {
    http_response_code(404);
    echo json_encode(['error' => 'Filen hittades inte']);
    exit;
}

unlink($path);
echo json_encode(['ok' => true]);
