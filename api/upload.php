<?php
header('Content-Type: application/json; charset=utf-8');

$imagesDir = dirname(__DIR__) . '/images';
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

$file = $_FILES['image'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Uppladdningen misslyckades (kod: ' . ($file['error'] ?? '?') . ')']);
    exit;
}

if ($file['size'] > 20 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'Filen är för stor (max 20 MB)']);
    exit;
}

$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
$mime    = mime_content_type($file['tmp_name']);
if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(['error' => 'Otillåten filtyp: ' . $mime]);
    exit;
}
$ext = $allowed[$mime];

$customName = trim($_POST['name'] ?? '');
$rawBase    = $customName !== '' ? $customName : pathinfo($file['name'], PATHINFO_FILENAME);
$baseName   = preg_replace('/[^a-zA-Z0-9_-]/', '_', $rawBase);
$baseName   = trim($baseName, '_');
if ($baseName === '') $baseName = 'bild';

$filename = $baseName . '.' . $ext;
$n = 2;
while (file_exists($imagesDir . '/' . $filename)) {
    $filename = $baseName . '_' . $n++ . '.' . $ext;
}

move_uploaded_file($file['tmp_name'], $imagesDir . '/' . $filename);

echo json_encode(['filename' => $filename]);
