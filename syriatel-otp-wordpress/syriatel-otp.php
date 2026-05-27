<?php
/**
 * Plugin Name: Syriatel OTP
 * Description: OTP via Syriatel BMS API with Syria Relay - For App Builder
 * Version: 1.0.0
 * Author: Dev
 */

if (!defined('ABSPATH')) {
    exit;
}

// ===================== CONFIGURATION =====================
// عدل هاي القيم حسب إعداداتك

define('SYRIATEL_RELAY_URL', 'https://eoclickandgo.sy/relay.php');              // ← رابط Relay السوري (Domain + forced resolve via cURL)
define('SYRIATEL_RELAY_SECRET', 'RMdu7huDbNrs3WlfCK2sdUYd7Ro2H4JZeUcWd5OwCFwmGaDDp4OUKoRUNokjs4Vv');   // ← Secret مشترك مع Relay (نفسه بـ relay.php)
define('SYRIATEL_OTP_PEPPER', 'AnotherRandomPepperForOtpHash456!');             // ← Pepper عشوائي لتخزين OTP
define('SYRIATEL_OTP_TTL', 300);  // 5 دقائق
define('SYRIATEL_RATE_LIMIT', 999); // عدد المحاولات (للتجربة فقط)
define('SYRIATEL_RATE_WINDOW', 900); // 15 دقيقة

// ===================== MAIN CLASS =====================

class Syriatel_OTP {

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route('syriatel-otp/v1', '/syriatel-send-otp', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'send_otp'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('syriatel-otp/v1', '/syriatel-verify-otp', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'verify_otp'],
            'permission_callback' => '__return_true',
        ]);
    }

    // Normalize phone: 09XXXXXXXX -> 9XXXXXXXX
    public static function normalize_phone($phone) {
        $phone = preg_replace('/\D/', '', $phone);
        if (strpos($phone, '09') === 0) {
            $phone = substr($phone, 1);
        }
        return $phone;
    }

    // ---------------- SEND OTP ----------------
    public static function send_otp(WP_REST_Request $request) {
        $phone = sanitize_text_field($request->get_param('phone'));

        if (empty($phone) || strlen(preg_replace('/\D/', '', $phone)) < 9) {
            return new WP_Error('invalid_phone', 'رقم الهاتف غير صالح', ['status' => 400]);
        }

        $normalized = self::normalize_phone($phone);

        // --- Rate Limit ---
        $rate_key = 'syriatel_otp_rate_' . $normalized;
        $attempts = get_transient($rate_key);
        if ($attempts !== false && (int) $attempts >= SYRIATEL_RATE_LIMIT) {
            return new WP_Error('rate_limited', 'عدد المحاولات تجاوز الحد. انتظر 15 دقيقة.', ['status' => 429]);
        }

        // --- Generate OTP ---
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash = hash('sha256', $otp . SYRIATEL_OTP_PEPPER);

        // --- Store in transient (5 min) ---
        $otp_key = 'syriatel_otp_' . $normalized;
        set_transient($otp_key, $hash, SYRIATEL_OTP_TTL);

        // --- Increment rate limit ---
        if ($attempts === false) {
            set_transient($rate_key, 1, SYRIATEL_RATE_WINDOW);
        } else {
            set_transient($rate_key, (int) $attempts + 1, SYRIATEL_RATE_WINDOW);
        }

        // --- Prepare message ---
        $message = 'رمز التحقق الخاص بك هو ' . $otp;

        // --- Send to Relay with HMAC ---
        $timestamp = time();
        $payload = $normalized . '|' . $message . '|' . $timestamp;
        $signature = hash_hmac('sha256', $payload, SYRIATEL_RELAY_SECRET);

        // Use file_get_contents with stream context (peer_name for SSL SNI)
        $relay_url = 'https://213.178.225.84/relay.php?token=' . urlencode(SYRIATEL_RELAY_SECRET);

        $postData = json_encode([
            'phone'     => '0' . $normalized,
            'message'   => $message,
            'timestamp' => $timestamp,
        ]);

        $headers = implode("\r\n", [
            'Host: eoclickandgo.sy',
            'Content-Type: application/json',
            'X-Relay-Signature: ' . $signature,
            'X-Relay-Timestamp: ' . $timestamp,
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => $headers,
                'content' => $postData,
                'timeout' => 30,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
                'peer_name'        => 'eoclickandgo.sy',
            ],
        ]);

        $body = @file_get_contents($relay_url, false, $context);

        if ($body === false) {
            $error = error_get_last();
            return new WP_Error('relay_error', 'فشل الاتصال بـ Relay: ' . ($error['message'] ?? 'Unknown error'), ['status' => 500]);
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('sms_failed', 'فشل إرسال الرسالة: Relay returned invalid JSON. Body: ' . substr($body, 0, 500), ['status' => 500]);
        }

        if (empty($data['success'])) {
            $error_msg = ($data['error'] ?? 'Unknown error');
            if (!empty($data['raw_response'])) {
                $error_msg .= ' | Raw: ' . $data['raw_response'];
            }
            if (!empty($data['http_code'])) {
                $error_msg .= ' | HTTP: ' . $data['http_code'];
            }
            return new WP_Error('sms_failed', 'فشل إرسال الرسالة: ' . $error_msg, ['status' => 500]);
        }

        return rest_ensure_response([
            'success'   => true,
            'message'   => 'تم إرسال رمز التحقق',
            'debug_msg_id' => $data['message_id'] ?? null, // للتجربة فقط، احذفه بالإنتاج
        ]);
    }

    // ---------------- VERIFY OTP ----------------
    public static function verify_otp(WP_REST_Request $request) {
        $phone = sanitize_text_field($request->get_param('phone'));
        $otp   = sanitize_text_field($request->get_param('otp'));

        if (empty($phone) || empty($otp)) {
            return new WP_Error('missing_params', 'رقم الهاتف والرمز مطلوبان', ['status' => 400]);
        }

        $normalized = self::normalize_phone($phone);
        $otp_key = 'syriatel_otp_' . $normalized;
        $stored_hash = get_transient($otp_key);

        if ($stored_hash === false) {
            return new WP_Error('expired_otp', 'انتهت صلاحية رمز التحقق', ['status' => 410]);
        }

        $input_hash = hash('sha256', $otp . SYRIATEL_OTP_PEPPER);

        if (!hash_equals($stored_hash, $input_hash)) {
            return new WP_Error('invalid_otp', 'رمز التحقق غير صحيح', ['status' => 400]);
        }

        // Delete OTP immediately (one-time use)
        delete_transient($otp_key);

        // Get or create user
        $user = self::get_or_create_user($normalized);

        if (is_wp_error($user)) {
            return new WP_Error('user_error', 'فشل إنشاء/استرجاع المستخدم: ' . $user->get_error_message(), ['status' => 500]);
        }

        // Generate token using App Builder logic
        $token = apply_filters('app_builder_generate_token', '', $user);

        if (empty($token)) {
            // Fallback if app builder filter not available
            $token = self::generate_fallback_token($user);
        }

        return rest_ensure_response([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'            => $user->ID,
                'user_email'    => $user->user_email,
                'display_name'  => $user->display_name,
                'user_nicename' => $user->user_nicename,
                'user_avatar'   => get_avatar_url($user->ID),
            ],
        ]);
    }

    // ---------------- USER MANAGEMENT ----------------
    public static function get_or_create_user($normalized_phone) {
        $users = get_users([
            'meta_key'   => 'syriatel_phone',
            'meta_value' => $normalized_phone,
            'number'     => 1,
        ]);

        if (!empty($users)) {
            return $users[0];
        }

        $username = 'user_' . $normalized_phone;
        $user_id = wp_insert_user([
            'user_login'   => $username,
            'user_pass'    => wp_generate_password(24, true),
            'user_email'   => $username . '@syriatel.local',
            'display_name' => '0' . $normalized_phone,
            'role'         => 'customer',
        ]);

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        update_user_meta($user_id, 'syriatel_phone', $normalized_phone);
        update_user_meta($user_id, 'billing_phone', '0' . $normalized_phone);

        return get_user_by('id', $user_id);
    }

    public static function generate_fallback_token($user) {
        $header  = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $time    = time();
        $payload = json_encode([
            'iss' => get_bloginfo('url'),
            'iat' => $time,
            'exp' => $time + (DAY_IN_SECONDS * 7),
            'sub' => $user->ID,
        ]);

        $base64_header  = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64_payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64_header . '.' . $base64_payload, SYRIATEL_RELAY_SECRET, true);
        $base64_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64_header . '.' . $base64_payload . '.' . $base64_signature;
    }
}

add_action('plugins_loaded', ['Syriatel_OTP', 'init']);
