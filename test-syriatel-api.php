<?php
/**
 * Syriatel Bulk SMS API Discovery Script
 * Upload this file to your WordPress root (e.g. public_html/test-syriatel-api.php)
 * Then visit: https://eoclickandgo.sy/test-syriatel-api.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(120);

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8"><title>اختبار API سرياتيل</title>';
echo '<style>body{font-family:Arial,sans-serif;background:#f5f5f5;padding:20px}h1{color:#333}.box{background:#fff;border:1px solid #ddd;padding:15px;margin:10px 0;border-radius:8px}.ok{color:green;font-weight:bold}.err{color:red;font-weight:bold}.info{color:#555}pre{background:#f0f0f0;padding:10px;overflow:auto;direction:ltr;text-align:left}</style>';
echo '</head><body>';
echo '<h1>🔍 اختبار API سرياتيل - Syriatel API Discovery</h1>';

// Config
$password = 'Pp@1234567';
$sender   = 'EO';
$template_ar = 'EO-API1';
$template_en = 'EO-API2';
$test_number = isset($_GET['phone']) ? $_GET['phone'] : '963900000000';
$test_otp    = '123456';
$users = ['20490', '41229894', 'EO', 'EO-API1'];

// Possible endpoints (http & https variants)
$endpoints = [
    'https://bulkmsg.syriatel.sy/api/send',
    'https://bulkmsg.syriatel.sy/send.aspx',
    'https://bulkmsg.syriatel.sy/api/v1/send',
    'https://bulkmsg.syriatel.sy/sms/send',
    'https://bulkmsg.syriatel.sy/rest/sms/send',
    'https://bulkmsg.syriatel.sy/api/sms/send',
    'https://bulkmsg.syriatel.sy/send',
    'https://bulkmsg.syriatel.sy/api/send_sms',
    'https://bulkmsg.syriatel.sy/index.php/api/send',
    'http://bulkmsg.syriatel.sy/api/send',
    'http://bulkmsg.syriatel.sy/send.aspx',
    'http://bulkmsg.syriatel.sy/api/v1/send',
    'http://bulkmsg.syriatel.sy/sms/send',
    'http://bulkmsg.syriatel.sy/send',
];

function try_request($label, $url, $method = 'POST', $headers = [], $body = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    
    echo '<div class="box">';
    echo "<strong>$label</strong><br>";
    echo "<span class='info'>URL: $url</span><br>";
    echo "<span class='info'>Method: $method | Time: {$total_time}s</span><br>";
    
    if ($curl_error) {
        echo "<span class='err'>❌ cURL Error: $curl_error</span><br>";
        echo '</div>';
        return false;
    }
    
    $is_success = ($http_code >= 200 && $http_code < 300) || ($http_code == 400) || ($http_code == 401) || ($http_code == 403);
    // 400/401/403 are actually GOOD for discovery - they mean endpoint exists!
    $css = $is_success ? 'ok' : 'err';
    echo "<span class='$css'>📡 HTTP Status: $http_code</span><br>";
    
    $decoded = json_decode($response, true);
    if ($decoded !== null) {
        echo '<pre>' . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
    } else {
        echo '<pre>' . htmlspecialchars(substr($response, 0, 2000)) . '</pre>';
    }
    echo '</div>';
    
    return $is_success;
}

echo '<div class="box">';
echo "<strong>⚙️ الإعدادات:</strong><br>";
echo "رقم الاختبار: $test_number<br>";
echo "كلمة المرور: $password<br>";
echo "المرسل: $sender<br>";
echo "القوالب: $template_ar / $template_en<br>";
echo '</div>';

echo '<form method="get">';
echo '<label>رقم الهاتف للاختبار (مع مقدمة الدولة): <input type="text" name="phone" value="' . htmlspecialchars($test_number) . '" style="width:200px"></label> ';
echo '<button type="submit">ابدأ الاختبار</button>';
echo '</form><hr>';

$found_any = false;

foreach ($endpoints as $endpoint) {
    echo "<h2>🌐 Testing Endpoint: $endpoint</h2>";
    
    foreach ($users as $user) {
        echo "<h3>👤 User: $user</h3>";
        
        // Pattern 1: JSON + Basic Auth
        $json_body = json_encode([
            'username' => $user,
            'password' => $password,
            'sender'   => $sender,
            'to'       => $test_number,
            'template' => $template_ar,
            'p1'       => $test_otp,
            'language' => 'ar',
        ]);
        if (try_request('1️⃣ JSON + Basic Auth', $endpoint, 'POST', [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode("$user:$password")
        ], $json_body)) {
            $found_any = true;
        }
        
        // Pattern 2: JSON body only (no auth header)
        if (try_request('2️⃣ JSON body only', $endpoint, 'POST', [
            'Content-Type: application/json'
        ], $json_body)) {
            $found_any = true;
        }
        
        // Pattern 3: Form URL-encoded
        $form_body = http_build_query([
            'username' => $user,
            'password' => $password,
            'sender'   => $sender,
            'to'       => $test_number,
            'template' => $template_ar,
            'p1'       => $test_otp,
        ]);
        if (try_request('3️⃣ Form URL-encoded', $endpoint, 'POST', [
            'Content-Type: application/x-www-form-urlencoded'
        ], $form_body)) {
            $found_any = true;
        }
        
        // Pattern 4: GET with query params
        $qs = http_build_query([
            'username' => $user,
            'password' => $password,
            'sender'   => $sender,
            'to'       => $test_number,
            'template' => $template_ar,
            'p1'       => $test_otp,
        ]);
        if (try_request('4️⃣ GET query params', "$endpoint?$qs", 'GET')) {
            $found_any = true;
        }
        
        // Pattern 5: Direct message (no template) - form
        $msg_body = http_build_query([
            'user'     => $user,
            'pass'     => $password,
            'from'     => $sender,
            'mobile'   => $test_number,
            'msg'      => "رمز التحقق الخاص بك هو $test_otp",
        ]);
        if (try_request('5️⃣ Direct message (form)', $endpoint, 'POST', [
            'Content-Type: application/x-www-form-urlencoded'
        ], $msg_body)) {
            $found_any = true;
        }
        
        // Pattern 6: Bearer token (password as token)
        if (try_request('6️⃣ Bearer token', $endpoint, 'POST', [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $password
        ], json_encode([
            'sender'   => $sender,
            'to'       => $test_number,
            'template' => $template_ar,
            'p1'       => $test_otp,
        ]))) {
            $found_any = true;
        }
        
        // Pattern 7: API Key header
        if (try_request('7️⃣ API Key header', $endpoint, 'POST', [
            'Content-Type: application/json',
            'X-API-Key: ' . $password
        ], $json_body)) {
            $found_any = true;
        }
        
        echo '<hr>';
    }
}

echo '<h2>🏁 النتيجة النهائية</h2>';
echo '<div class="box">';
if ($found_any) {
    echo '<span class="ok">✅ تم العثور على استجابة من API! انظر أعلاه للتفاصيل.</span>';
} else {
    echo '<span class="err">❌ لم يتم العثور على أي استجابة ناجحة.</span><br>';
    echo 'الأسباب المحتملة:<br>';
    echo '1. عنوان API مختلف عن التوقعات<br>';
    echo '2. API محمي بـ IP Whitelist (لا يمكن الوصول إلا من IPs معينة)<br>';
    echo '3. يتطلب VPN أو اتصال داخلي بشبكة سرياتيل<br>';
    echo '4. بيانات الاعتماد غير صحيحة<br>';
    echo '5. يتطلب رقم هاتف حقيقي (وليس وهمي)<br>';
}
echo '</div>';

echo '</body></html>';
