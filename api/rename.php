<?php
header('Content-Type: application/json; charset=utf-8');

$body    = json_decode(file_get_contents('php://input'), true);
$from    = basename($body['from'] ?? '');
$newBase = preg_replace('/[^a-zA-Z0-9_-]/', '', $body['to'] ?? '');

if (!$from || !$newBase) {
    http_response_code(400);
    echo json_encode(['error' => 'Ogiltiga parametrar']);
    exit;
}

$imagesDir = dirname(__DIR__) . '/images';
$fromPath  = $imagesDir . '/' . $from;

if (!file_exists($fromPath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Filen hittades inte']);
    exit;
}

$ext      = pathinfo($from, PATHINFO_EXTENSION);
$toFile   = $newBase . ($ext ? '.' . $ext : '');
$toPath   = $imagesDir . '/' . $toFile;

if ($toFile !== $from && file_exists($toPath)) {
    http_response_code(409);
    echo json_encode(['error' => 'En fil med det namnet finns redan']);
    exit;
}

rename($fromPath, $toPath);
echo json_encode(['ok' => true, 'filename' => $toFile]);
