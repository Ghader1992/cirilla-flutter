<?php
/**
 * App Builder API Key Authentication Integration
 *
 * This class integrates Push Notification plugin with App Builder's API Key system.
 * It allows Push Notification APIs to use the same app_builder_key and app_builder_secret
 * as App Builder for authentication.
 *
 * @link       https://appcheap.io
 * @since      2.0.4
 * @package    PushNotify
 */

namespace PushNotify\Api;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use WP_REST_Request;

/**
 * AppBuilderKey Class
 * 
 * Provides API key authentication for Push Notification endpoints
 * using App Builder's API keys system.
 */
class AppBuilderKey {

    /**
     * Singleton instance
     *
     * @var AppBuilderKey|null
     */
    private static $instance = null;

    /**
     * Table name for API keys (App Builder's table)
     *
     * @var string
     */
    private $table_name;

    /**
     * Whether App Builder is active
     *
     * @var bool
     */
    private $app_builder_active = false;

    /**
     * Excluded endpoints that don't require authentication
     *
     * @var array
     */
    private $excluded_endpoints = array();

    /**
     * Get singleton instance
     *
     * @return AppBuilderKey
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'app_builder_api_keys';
        $this->app_builder_active = $this->is_app_builder_active();
        $this->excluded_endpoints = $this->get_excluded_endpoints();
    }

    /**
     * Initialize hooks
     */
    public function init() {
        // If App Builder is active, let it handle ALL authentication for push-notify
        // App Builder already checks for push-notify endpoints in is_app_builder_request()
        // We only need to add our own authentication if App Builder is NOT active
        if ( ! $this->app_builder_active ) {
            add_filter( 'rest_authentication_errors', array( $this, 'authenticate' ), 100 );
        }
    }

    /**
     * Check if App Builder plugin is active
     *
     * @return bool
     */
    private function is_app_builder_active() {
        return class_exists( 'AppBuilder\\GenerateKey\\AppBuilderKey' ) || 
               defined( 'APP_BUILDER_VERSION' ) ||
               is_plugin_active( 'app-builder/app-builder.php' );
    }

    /**
     * Authenticate API request using App Builder's app_builder_key and app_builder_secret
     *
     * @param WP_Error|null|bool $result Authentication result
     * @return WP_Error|null|bool
     */
    public function authenticate( $result ) {
        // Don't override existing authentication errors
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Only authenticate for push-notify endpoints
        if ( ! $this->is_push_notify_request() ) {
            return $result;
        }

        // Check if API key authentication is enabled
        if ( ! $this->is_api_key_auth_enabled() ) {
            return $result;
        }

        // Check if current endpoint is excluded from authentication
        if ( $this->is_excluded_endpoint() ) {
            return $result;
        }

        // ALWAYS require API key - NO bypass for admin users
        // This ensures mobile/web apps MUST use API keys

        // Get credentials from request
        $app_builder_key    = $this->get_app_builder_key();
        $app_builder_secret = $this->get_app_builder_secret();

        // API key is REQUIRED for all push-notify endpoints - NO EXCEPTIONS
        if ( empty( $app_builder_key ) || empty( $app_builder_secret ) ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'API Key required. Please provide App-Builder-Key and App-Builder-Secret headers.', 'push-notify' ),
                array( 'status' => 401 )
            );
        }

        // Check if App Builder's API keys table exists
        if ( ! $this->api_keys_table_exists() ) {
            return new WP_Error(
                'api_keys_not_setup',
                __( 'API Keys table not found. Please install and activate App Builder plugin, then generate API keys.', 'push-notify' ),
                array( 'status' => 500 )
            );
        }

        // Validate credentials using App Builder's table
        $validation = $this->validate_credentials( $app_builder_key, $app_builder_secret );

        if ( is_wp_error( $validation ) ) {
            return $validation;
        }

        return true;
    }

    /**
     * Check if current request is for push-notify API
     *
     * @return bool
     */
    private function is_push_notify_request() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        return strpos( $request_uri, '/wp-json/push-notify/' ) !== false;
    }

    /**
     * Check if API key authentication is enabled
     *
     * @return bool
     */
    private function is_api_key_auth_enabled() {
        return apply_filters( 'push_notify_api_key_auth_enabled', true );
    }

    /**
     * Get excluded endpoints that don't require authentication
     *
     * @return array
     */
    private function get_excluded_endpoints() {
        $default_excluded = array(
            // Add any push-notify endpoints that should be public
            // Example: '/push-notify/v1/public-endpoint',
        );

        /**
         * Filter excluded endpoints from API key authentication
         *
         * @param array $excluded_endpoints Array of endpoint paths to exclude
         */
        return apply_filters( 'push_notify_api_key_excluded_endpoints', $default_excluded );
    }

    /**
     * Check if current endpoint is excluded from authentication
     *
     * @return bool
     */
    private function is_excluded_endpoint() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        foreach ( $this->excluded_endpoints as $endpoint ) {
            // Support both full path and partial path matching
            if ( strpos( $request_uri, $endpoint ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if App Builder API keys table exists
     *
     * @return bool
     */
    private function api_keys_table_exists() {
        global $wpdb;
        $table_exists = $wpdb->get_var( 
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $this->table_name
            )
        );
        return ! empty( $table_exists );
    }

    /**
     * Get app builder key from request
     *
     * Supports multiple header formats (same as App Builder):
     * 1. App-Builder-Key: abk_xxx (recommended)
     * 2. Authorization: Basic base64(abk_xxx:abs_xxx)
     * 3. Query param: ?app_builder_key=abk_xxx
     *
     * @return string
     */
    private function get_app_builder_key() {
        // Method 1: App-Builder-Key header (recommended)
        if ( isset( $_SERVER['HTTP_APP_BUILDER_KEY'] ) ) {
            return sanitize_text_field( wp_unslash( $_SERVER['HTTP_APP_BUILDER_KEY'] ) );
        }

        // Method 2: Authorization header (Basic Auth)
        if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
            $auth = sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) );
            if ( strpos( $auth, 'Basic ' ) === 0 ) {
                $decoded = base64_decode( substr( $auth, 6 ) );
                $parts   = explode( ':', $decoded, 2 );
                if ( count( $parts ) === 2 ) {
                    return $parts[0];
                }
            }
        }

        // Method 3: Query parameter
        if ( isset( $_GET['app_builder_key'] ) ) {
            return sanitize_text_field( wp_unslash( $_GET['app_builder_key'] ) );
        }

        return '';
    }

    /**
     * Get app builder secret from request
     *
     * @return string
     */
    private function get_app_builder_secret() {
        // Method 1: App-Builder-Secret header (recommended)
        if ( isset( $_SERVER['HTTP_APP_BUILDER_SECRET'] ) ) {
            return sanitize_text_field( wp_unslash( $_SERVER['HTTP_APP_BUILDER_SECRET'] ) );
        }

        // Method 2: Authorization header (Basic Auth)
        if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
            $auth = sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) );
            if ( strpos( $auth, 'Basic ' ) === 0 ) {
                $decoded = base64_decode( substr( $auth, 6 ) );
                $parts   = explode( ':', $decoded, 2 );
                if ( count( $parts ) === 2 ) {
                    return $parts[1];
                }
            }
        }

        // Method 3: Query parameter
        if ( isset( $_GET['app_builder_secret'] ) ) {
            return sanitize_text_field( wp_unslash( $_GET['app_builder_secret'] ) );
        }

        return '';
    }

    /**
     * Validate credentials against App Builder's API keys
     *
     * @param string $app_builder_key App Builder key
     * @param string $app_builder_secret App Builder secret
     * @return bool|WP_Error
     */
    private function validate_credentials( $app_builder_key, $app_builder_secret ) {
        global $wpdb;

        // Check if table exists
        if ( ! $this->api_keys_table_exists() ) {
            return new WP_Error(
                'api_keys_not_setup',
                __( 'API Keys are not configured. Please set up API Keys in App Builder plugin.', 'push-notify' ),
                array( 'status' => 500 )
            );
        }

        // Hash the app builder key for lookup
        $hashed_key = $this->hash_key( $app_builder_key );

        // Get API key from database
        $api_key = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE app_builder_key = %s AND status = 'active'",
                $hashed_key
            )
        );

        if ( empty( $api_key ) ) {
            return new WP_Error(
                'invalid_api_key',
                __( 'Invalid API key. Please check your app builder key.', 'push-notify' ),
                array( 'status' => 401 )
            );
        }

        // Check expiration
        if ( ! empty( $api_key->expires_at ) && strtotime( $api_key->expires_at ) < time() ) {
            return new WP_Error(
                'api_key_expired',
                __( 'API key has expired. Please generate a new key.', 'push-notify' ),
                array( 'status' => 401 )
            );
        }

        // Verify app builder secret
        if ( ! $this->verify_secret( $app_builder_secret, $api_key->app_builder_secret ) ) {
            return new WP_Error(
                'invalid_api_secret',
                __( 'Invalid API secret. Please check your app builder secret.', 'push-notify' ),
                array( 'status' => 401 )
            );
        }

        // Set current user
        $user = get_user_by( 'id', $api_key->user_id );
        if ( $user ) {
            wp_set_current_user( $user->ID );
        }

        // Update last access time
        $wpdb->update(
            $this->table_name,
            array(
                'last_access'   => current_time( 'mysql' ),
                'request_count' => $api_key->request_count + 1,
            ),
            array( 'key_id' => $api_key->key_id ),
            array( '%s', '%d' ),
            array( '%d' )
        );

        return true;
    }

    /**
     * Hash a key for secure storage/comparison
     *
     * @param string $key The key to hash
     * @return string
     */
    private function hash_key( $key ) {
        return hash( 'sha256', $key );
    }

    /**
     * Verify a secret against stored hash
     *
     * @param string $secret The secret to verify
     * @param string $hash The stored hash
     * @return bool
     */
    private function verify_secret( $secret, $hash ) {
        return hash_equals( $hash, hash( 'sha256', $secret ) );
    }

    /**
     * Permission callback for API endpoints that require API key
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function api_key_permission_check( WP_REST_Request $request ) {
        // ALWAYS require API key - NO bypass for admin users
        // This ensures mobile/web apps MUST use API keys

        // Get credentials
        $app_builder_key    = $this->get_app_builder_key();
        $app_builder_secret = $this->get_app_builder_secret();

        // API key is REQUIRED - NO EXCEPTIONS
        if ( empty( $app_builder_key ) || empty( $app_builder_secret ) ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'API Key required. Please provide App-Builder-Key and App-Builder-Secret headers.', 'push-notify' ),
                array( 'status' => 401 )
            );
        }

        // Check if table exists
        if ( ! $this->api_keys_table_exists() ) {
            return new WP_Error(
                'api_keys_not_setup',
                __( 'API Keys table not found. Please install App Builder plugin and generate API keys.', 'push-notify' ),
                array( 'status' => 500 )
            );
        }

        return $this->validate_credentials( $app_builder_key, $app_builder_secret );
    }
}
