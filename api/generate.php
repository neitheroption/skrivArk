<?php
header('Content-Type: application/json; charset=utf-8');

$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'OPENAI_API_KEY saknas i miljövariabler']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$word = trim($body['word'] ?? '');

if ($word === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Ord saknas']);
    exit;
}

$prompt = "Skapa en teckning av en {$word} för användning i ett skrivhäfte för ett barn - gör den tydlig, gärna med klara färger och väldefinerade konturer. Låt bakgrunden vara genomskinlig.";

$payload = json_encode([
    'model'  => 'gpt-image-1',
    'prompt' => $prompt,
    'n'      => 1,
    'size'   => '1024x1024',
]);

$ch = curl_init('https://api.openai.com/v1/images/generations');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_TIMEOUT => 90,
]);

$raw  = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resp = json_decode($raw, true);

if ($http !== 200) {
    http_response_code(502);
    echo json_encode(['error' => $resp['error']['message'] ?? 'OpenAI-fel (HTTP ' . $http . ')']);
    exit;
}

// gpt-image-1 returns b64_json directly
$b64 = $resp['data'][0]['b64_json'] ?? null;
if (!$b64) {
    http_response_code(502);
    echo json_encode(['error' => 'Ingen bild returnerades fran OpenAI', 'raw' => $resp]);
    exit;
}
$imgData = base64_decode($b64);

$imagesDir = dirname(__DIR__) . '/images';
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

$safe = preg_replace('/[^a-zA-Z0-9_-]/', '', $word);
if ($safe === '') $safe = 'bild';

$filename = $safe . '.png';
$n = 2;
while (file_exists($imagesDir . '/' . $filename)) {
    $filename = $safe . '_' . $n++ . '.png';
}

file_put_contents($imagesDir . '/' . $filename, $imgData);


echo json_encode(['filename' => $filename, 'word' => $word]);
