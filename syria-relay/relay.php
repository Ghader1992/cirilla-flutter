<?php
/**
 * Syria Relay Server - Syriatel BMS API
 * 
 * هذا الملف يرفع على الهوست السوري
 * المسار: /relay.php (أو أي مكان بـ httpdocs)
 */

// ===================== CONFIGURATION =====================

$RELAY_SECRET       = 'RMdu7huDbNrs3WlfCK2sdUYd7Ro2H4JZeUcWd5OwCFwmGaDDp4OUKoRUNokjs4Vv'; // ← نفس Secret الـ WordPress
$ALLOWED_IPS        = ['198.38.94.5'];           // ← IP سيرفر WordPress الأساسي (الإمارات)
$SYRIATEL_USERNAME  = 'EO-API1';
$SYRIATEL_PASSWORD  = 'Pp@1234567';              // ← باسورد Syriatel
$SYRIATEL_SENDER    = 'EO';

// ===================== SECURITY CHECKS =====================

header('Content-Type: application/json; charset=utf-8');

// 1. Token verification (from URL ?token=...)
$token = $_GET['token'] ?? '';
if (!hash_equals($RELAY_SECRET, $token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

// 2. IP Whitelist
$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($client_ip, $ALLOWED_IPS, true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'IP not allowed: ' . $client_ip]);
    exit;
}

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (empty($data['phone']) || empty($data['message']) || !isset($data['timestamp'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Extract headers (case-insensitive)
$headers = [];
foreach (getallheaders() as $key => $value) {
    $headers[strtolower($key)] = $value;
}

$signature = $headers['x-relay-signature'] ?? '';
$timestamp = (int) $data['timestamp'];
$phone     = $data['phone'];
$message   = $data['message'];

// HMAC Verification
$normalized_phone = preg_replace('/\D/', '', $phone);
if (strpos($normalized_phone, '09') === 0) {
    $normalized_phone = substr($normalized_phone, 1);
}

$payload   = $normalized_phone . '|' . $message . '|' . $timestamp;
$expected  = hash_hmac('sha256', $payload, $RELAY_SECRET);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid signature',
    ]);
    exit;
}

// Timestamp check (±60 seconds)
if (abs(time() - $timestamp) > 60) {
    http_response_code(410);
    echo json_encode(['success' => false, 'error' => 'Request expired']);
    exit;
}

// ===================== SEND TO SYRIATEL BMS =====================

$url = 'http://bms.syriatel.sy/API/SendSMS.aspx?' . http_build_query([
    'user_name' => $SYRIATEL_USERNAME,
    'password'  => $SYRIATEL_PASSWORD,
    'msg'       => $message,
    'sender'    => $SYRIATEL_SENDER,
    'to'        => $phone,
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
]);

$response   = curl_exec($ch);
$http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'cURL error: ' . $curl_error]);
    exit;
}

$response = trim($response);

// Success rule: response is digits only = Message ID
if ($http_code == 200 && ctype_digit($response)) {
    echo json_encode([
        'success'    => true,
        'message_id' => $response,
    ]);
    exit;
}

// Failure
http_response_code(502);
echo json_encode([
    'success'      => false,
    'error'        => 'Syriatel API returned non-numeric response',
    'raw_response' => $response,
    'http_code'    => $http_code,
]);
