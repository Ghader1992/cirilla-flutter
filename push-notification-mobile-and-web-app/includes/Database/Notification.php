<?php

/**
 * class Notification
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 *
 */

namespace PushNotify\Database;

defined( 'ABSPATH' ) || exit;

class Notification {

	public function __construct() {
	}

	/**
	 *
	 * Save a notification
	 *
	 * @param int $user_id
	 * @param array $payload
	 * @param array $action
	 *
	 * @return int
	 */
	public function save_notification( int $user_id, array $payload, array $action ): int {
		global $wpdb;

		$table = $wpdb->prefix . "pn_user_notifications";

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO " . $table . " (`user_id`, `payload`, `action`, `created_at`, `seen`) VALUES (%d, %s, %s, %s, 0)",
				$user_id,
				serialize( $payload ),
				serialize( $action ),
				$this->datetime(),
			)
		);

		return $wpdb->insert_id;
	}

	/**
	 *
	 * Retrieve a notification
	 *
	 * @param $id
	 *
	 * @return array|object|null
	 */
	public function get_notification( $id ) {
		global $wpdb;

		$table = $wpdb->prefix . "pn_user_notifications";

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT id,payload,action,created_at,seen FROM " . $table . " WHERE id = %d", $id )
		);
	}

	/**
	 *
	 * Get user notifications
	 *
	 * @param int $user_id
	 * @param int $page
	 * @param int $per_page
	 *
	 * @return array|object|null
	 */
	public function get_notifications( int $user_id, int $page = 1, int $per_page = 10 ) {
		global $wpdb;
		$table = $wpdb->prefix . "pn_user_notifications";

		$offset = ( $page * $per_page ) - $per_page;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id,payload,action,created_at,seen FROM " . $table . " WHERE user_id = %d ORDER BY id DESC LIMIT %d, %d",
				$user_id,
				$offset,
				$per_page
			)
		);
	}

	/**
	 *
	 * Delete the notification.
	 *
	 * @param int $id
	 *
	 * @return bool|int
	 */
	public function remove_notification( int $id ) {
		global $wpdb;
		$table = $wpdb->prefix . "pn_user_notifications";

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . $table . " WHERE id = %d",
				$id,
			)
		);
	}

	/**
	 *
	 * Delete all notifications.
	 *
	 * @param int $user_id
	 *
	 * @return bool|int
	 */
	public function remove_notifications( int $user_id ) {
		global $wpdb;
		$table = $wpdb->prefix . "pn_user_notifications";

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . $table . " WHERE user_id = %d",
				$user_id,
			)
		);
	}

	/**
	 *
	 * Get un read notification
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function un_read_notification( int $user_id ): int {
		global $wpdb;

		$table = $wpdb->prefix . "pn_user_notifications";

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE seen = 0 AND user_id = %d", $user_id
			)
		);

		return (int) $total;
	}

	/**
	 *
	 * Update status notification
	 *
	 * @param int $id
	 * @param int $user_id
	 *
	 * @return bool|int
	 */
	public function read_notification( int $id, int $user_id ) {
		global $wpdb;

		$table = $wpdb->prefix . "pn_user_notifications";


		return $wpdb->query( $wpdb->prepare(
			"UPDATE $table SET seen=1 WHERE id = %d AND user_id = %d",
			$id,
			$user_id
		) );
	}

	/**
	 * Get MySQL datetime.
	 *
	 * @param int $offset Seconds, can pass negative int.
	 *
	 * @return string
	 */
	protected function datetime( int $offset = 0 ): string {
		$timestamp = time() + $offset;

		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}
}
