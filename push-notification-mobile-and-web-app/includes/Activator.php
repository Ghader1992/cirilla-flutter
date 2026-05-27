<?php

/**
 * Fired during plugin activation
 *
 * @link       https://appcheap.io
 * @since      1.0.0
 *
 */

namespace PushNotify;

defined( 'ABSPATH' ) || exit;

class Activator {

	/**
	 * Do something when plugin active
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function activate() {
		self::create_tables();
	}

	/**
	 * Creates database tables which the plugin needs to function.
	 *
	 * @access private
	 * @static
	 * @since  1.0.5
	 * @author Ngoc Dang
	 * @global $wpdb
	 */
	private function create_tables() {
		global $wpdb;

		// Disables showing of database errors.
		$wpdb->hide_errors();

		// Default character set and collation for the table
		$collate = $wpdb->get_charset_collate();

		// Create tokens table
		$table_tokens = "CREATE TABLE {$wpdb->prefix}pn_notification_tokens ( 
    		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
     		user_id INT NOT NULL,
      		token VARCHAR(200) NOT NULL,
        	PRIMARY KEY (id),
        	UNIQUE KEY (token)
        ) $collate;";

		// Create user notifications table
		$table_user_notifications = "CREATE TABLE {$wpdb->prefix}pn_user_notifications ( 
    		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
     		user_id INT NOT NULL,
      		payload text NOT NULL,
      		action text NOT NULL,
      		seen TINYINT NOT NULL DEFAULT 0,
      		created_at datetime NOT NULL,
        	PRIMARY KEY (id)
        ) $collate;";

		// Load dbDelta if it is not loaded by default
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Execute the SQL
		dbDelta( $table_tokens );
		dbDelta( $table_user_notifications );

		// Save db version
		add_option( 'push_notify_version', PUSH_NOTIFY_DB_VERSION );
	}
}
