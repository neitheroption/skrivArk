<?php
header('Content-Type: application/json; charset=utf-8');

$imagesDir = dirname(__DIR__) . '/images';
if (!is_dir($imagesDir)) {
    echo json_encode([]);
    exit;
}

$files = [];
foreach (new DirectoryIterator($imagesDir) as $f) {
    if ($f->isDot() || !$f->isFile()) continue;
    if (!in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) continue;
    $files[] = ['name' => $f->getFilename(), 'mtime' => $f->getMTime()];
}

usort($files, fn($a, $b) => $b['mtime'] - $a['mtime']);

echo json_encode(array_column($files, 'name'));
