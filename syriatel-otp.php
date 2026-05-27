<?php
/**
 * Plugin Name: Syriatel OTP
 * Description: OTP via Syriatel BMS API using Syria Relay Server - For App Builder
 * Version: 1.0.2
 * Author: Dev
 */

if (!defined('ABSPATH')) {
    exit;
}

// ===================== CONFIGURATION =====================

define('SYRIATEL_RELAY_HOST', 'eoclickandgo.sy');
define('SYRIATEL_RELAY_IP', '213.178.225.84');
define('SYRIATEL_RELAY_URL', 'https://eoclickandgo.sy/relay.php');

define('SYRIATEL_RELAY_SECRET', 'RMdu7huDbNrs3WlfCK2sdUYd7Ro2H4JZeUcWd5OwCFwmGaDDp4OUKoRUNokjs4Vv');
define('SYRIATEL_OTP_PEPPER', 'AnotherRandomPepperForOtpHash456!');

define('SYRIATEL_OTP_TTL', 300);      // 5 minutes
define('SYRIATEL_RATE_LIMIT', 3);     // Max attempts per phone
define('SYRIATEL_RATE_WINDOW', 900);  // 15 minutes

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

        if (strpos($phone, '00963') === 0) {
            $phone = substr($phone, 5);
        }
        if (strpos($phone, '963') === 0) {
            $phone = substr($phone, 3);
        }
        if (strpos($phone, '09') === 0) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    public static function format_phone_for_syriatel($normalized_phone) {
        return '0' . $normalized_phone;
    }

    // ===================== SEND OTP =====================

    public static function send_otp(WP_REST_Request $request) {
        $phone = sanitize_text_field($request->get_param('phone'));

        if (empty($phone)) {
            return new WP_Error('invalid_phone', 'رقم الهاتف مطلوب', ['status' => 400]);
        }

        $normalized = self::normalize_phone($phone);

        if (!preg_match('/^9\d{8}$/', $normalized)) {
            return new WP_Error('invalid_phone', 'رقم الهاتف غير صالح. يجب أن يكون رقم سوري مثل 09XXXXXXXX', ['status' => 400]);
        }

        // ---------- Rate Limit ----------
        $rate_key = 'syriatel_otp_rate_' . $normalized;
        $attempts = get_transient($rate_key);

        if ($attempts !== false && (int) $attempts >= SYRIATEL_RATE_LIMIT) {
            return new WP_Error('rate_limited', 'عدد المحاولات تجاوز الحد. انتظر 15 دقيقة.', ['status' => 429]);
        }

        // ---------- Generate OTP ----------
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash = hash('sha256', $otp . SYRIATEL_OTP_PEPPER);

        $otp_key = 'syriatel_otp_' . $normalized;
        set_transient($otp_key, $hash, SYRIATEL_OTP_TTL);

        if ($attempts === false) {
            set_transient($rate_key, 1, SYRIATEL_RATE_WINDOW);
        } else {
            set_transient($rate_key, (int) $attempts + 1, SYRIATEL_RATE_WINDOW);
        }

        // ---------- Prepare Relay Payload ----------
        $message = 'رمز التحقق الخاص بك هو ' . $otp;
        $timestamp = time();

        $payload_for_signature = $normalized . '|' . $message . '|' . $timestamp;
        $signature = hash_hmac('sha256', $payload_for_signature, SYRIATEL_RELAY_SECRET);

        $post_data = [
            'phone'     => self::format_phone_for_syriatel($normalized),
            'message'   => $message,
            'timestamp' => $timestamp,
        ];

        $relay_response = self::send_to_relay($post_data, $signature, $timestamp);

        if (is_wp_error($relay_response)) {
            return $relay_response;
        }

        if (empty($relay_response['success'])) {
            $error_msg = $relay_response['error'] ?? 'Unknown relay error';

            if (!empty($relay_response['raw_response'])) {
                $error_msg .= ' | Raw: ' . $relay_response['raw_response'];
            }
            if (!empty($relay_response['http_code'])) {
                $error_msg .= ' | HTTP: ' . $relay_response['http_code'];
            }

            return new WP_Error('sms_failed', 'فشل إرسال الرسالة: ' . $error_msg, ['status' => 500]);
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'تم إرسال رمز التحقق',
        ]);
    }

    // ===================== RELAY REQUEST =====================

    private static function send_to_relay(array $post_data, string $signature, int $timestamp) {
        $relay_url = SYRIATEL_RELAY_URL . '?token=' . urlencode(SYRIATEL_RELAY_SECRET);

        $json_body = json_encode($post_data, JSON_UNESCAPED_UNICODE);

        if ($json_body === false) {
            return new WP_Error('json_error', 'فشل تجهيز بيانات الطلب JSON', ['status' => 500]);
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $relay_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json_body,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,

            // Force DNS resolve to Syria IP (Domain has no public DNS)
            CURLOPT_RESOLVE        => [
                SYRIATEL_RELAY_HOST . ':443:' . SYRIATEL_RELAY_IP,
            ],

            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json; charset=utf-8',
                'Accept: application/json',
                'X-Relay-Signature: ' . $signature,
                'X-Relay-Timestamp: ' . $timestamp,
            ],

            // Temporary: Domain has no valid SSL certificate
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,

            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $curl_errno = curl_errno($ch);

        curl_close($ch);

        if ($body === false || $curl_errno) {
            return new WP_Error(
                'relay_connection_error',
                'فشل الاتصال بـ Relay: ' . ($curl_error ?: 'Unknown cURL error'),
                ['status' => 500]
            );
        }

        if ($http_code < 200 || $http_code >= 300) {
            return new WP_Error(
                'relay_http_error',
                'Relay HTTP error: ' . $http_code . ' Body: ' . substr((string) $body, 0, 500),
                ['status' => 500]
            );
        }

        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'relay_invalid_json',
                'Relay returned invalid JSON. Body: ' . substr((string) $body, 0, 500),
                ['status' => 500]
            );
        }

        return $decoded;
    }

    // ===================== VERIFY OTP =====================

    public static function verify_otp(WP_REST_Request $request) {
        $phone = sanitize_text_field($request->get_param('phone'));
        $otp   = sanitize_text_field($request->get_param('otp'));

        if (empty($phone) || empty($otp)) {
            return new WP_Error('missing_params', 'رقم الهاتف والرمز مطلوبان', ['status' => 400]);
        }

        $normalized = self::normalize_phone($phone);

        if (!preg_match('/^9\d{8}$/', $normalized)) {
            return new WP_Error('invalid_phone', 'رقم الهاتف غير صالح', ['status' => 400]);
        }

        $otp_key = 'syriatel_otp_' . $normalized;
        $stored_hash = get_transient($otp_key);

        if ($stored_hash === false) {
            return new WP_Error('expired_otp', 'انتهت صلاحية رمز التحقق', ['status' => 410]);
        }

        $input_hash = hash('sha256', $otp . SYRIATEL_OTP_PEPPER);

        if (!hash_equals($stored_hash, $input_hash)) {
            return new WP_Error('invalid_otp', 'رمز التحقق غير صحيح', ['status' => 400]);
        }

        delete_transient($otp_key);

        $user = self::get_or_create_user($normalized);

        if (is_wp_error($user)) {
            return new WP_Error(
                'user_error',
                'فشل إنشاء/استرجاع المستخدم: ' . $user->get_error_message(),
                ['status' => 500]
            );
        }

        $token = apply_filters('app_builder_generate_token', '', $user);

        if (empty($token)) {
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
                'first_name'    => $user->first_name,
                'last_name'     => $user->last_name,
                'user_avatar'   => get_avatar_url($user->ID),
            ],
        ]);
    }

    // ===================== USER MANAGEMENT =====================

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

        $existing_user = get_user_by('login', $username);
        if ($existing_user) {
            update_user_meta($existing_user->ID, 'syriatel_phone', $normalized_phone);
            update_user_meta($existing_user->ID, 'billing_phone', '0' . $normalized_phone);
            return $existing_user;
        }

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

        // ✅ FIX: Set first_name and last_name to empty string
        wp_update_user([
            'ID'         => $user_id,
            'first_name' => '',
            'last_name'  => '',
        ]);

        update_user_meta($user_id, 'syriatel_phone', $normalized_phone);
        update_user_meta($user_id, 'billing_phone', '0' . $normalized_phone);

        return get_user_by('id', $user_id);
    }

    // ===================== FALLBACK TOKEN =====================

    public static function generate_fallback_token($user) {
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256',
        ]);

        $time = time();

        $payload = json_encode([
            'iss' => get_bloginfo('url'),
            'iat' => $time,
            'exp' => $time + (DAY_IN_SECONDS * 7),
            'sub' => $user->ID,
        ]);

        $base64_header = self::base64url_encode($header);
        $base64_payload = self::base64url_encode($payload);

        $signature = hash_hmac(
            'sha256',
            $base64_header . '.' . $base64_payload,
            SYRIATEL_RELAY_SECRET,
            true
        );

        $base64_signature = self::base64url_encode($signature);

        return $base64_header . '.' . $base64_payload . '.' . $base64_signature;
    }

    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

add_action('plugins_loaded', ['Syriatel_OTP', 'init']);
