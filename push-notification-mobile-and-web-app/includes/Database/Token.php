<?php

/**
 * class Token
 *
 * @link       https://appcheap.io
 * @since      1.0.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 *
 */

namespace PushNotify\Database;

defined( 'ABSPATH' ) || exit;

class Token {

	public function __construct() {
	}

	/**
	 *
	 * Get token by user id
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public function get_tokens( $user_id ): array {
		global $wpdb;

		$table = $wpdb->prefix . "pn_notification_tokens";

		$tokens = $wpdb->get_results(
			$wpdb->prepare( "SELECT token FROM " . $table . " WHERE user_id = %d", $user_id )
		);

		if ( empty( $tokens ) ) {
			return [];
		}

		return $tokens;

	}

	/**
	 *
	 * Update | Add token
	 *
	 * @param int $user_id
	 * @param string $token
	 *
	 * @return bool|int
	 */
	public function add_token( int $user_id, string $token ) {
		global $wpdb;

		$table = $wpdb->prefix . "pn_notification_tokens";

		return $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO " . $table . " (`token`, `user_id`) VALUES (%s, %d)
 					ON DUPLICATE KEY UPDATE `token` = VALUES(`token`), `user_id` = VALUES(`user_id`)",
				$token,
				$user_id,
			)
		);
	}

	/**
	 *
	 * Remove token by user id and token
	 *
	 * @param $user_id
	 * @param $token
	 *
	 * @return bool|int
	 */
	public function remove_token( $user_id, $token ) {
		global $wpdb;

		if ( ! $user_id || ! $token ) {
			return false;
		}

		$table = $wpdb->prefix . "pn_notification_tokens";

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . $table . " WHERE user_id = %d AND token = %s",
				(int) $user_id,
				$token,
			)
		);
	}

	/**
	 *
	 * Remove token by user id
	 *
	 * @param $user_id
	 *
	 * @return bool|int
	 */
	public function remove_token_by_user_id( $user_id ) {
		global $wpdb;

		if ( ! $user_id ) {
			return false;
		}

		$table = $wpdb->prefix . "pn_notification_tokens";

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . $table . " WHERE user_id = %d",
				(int) $user_id,
			)
		);
	}

	/**
	 *
	 * Remove token by token
	 *
	 * @param $token
	 *
	 * @return bool|int
	 */
	public function remove_token_by_token( $token ) {
		global $wpdb;

		if ( ! $token ) {
			return false;
		}

		$table = $wpdb->prefix . "pn_notification_tokens";

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . $table . " WHERE token = %s",
				$token,
			)
		);
	}

	/**
	 *
	 * Remove tokens expired
	 *
	 * @param array $tokens_expired
	 *
	 * @return bool
	 */
	public function remove_tokens_expired( array $tokens_expired = [] ): bool {
		global $wpdb;

		$table = $wpdb->prefix . "pn_notification_tokens";

		if ( ! empty( $tokens_expired ) ) {
			foreach ( $tokens_expired as $token ) {
				$wpdb->delete(
					$table,
					array(
						"token" => trim( $token )
					)
				);
			}

			return true;
		}

		return false;
	}

	/**
	 *
	 * Get tokens by user id
	 *
	 * @param $user_id
	 *
	 * @return array|object
	 */
	public function get_tokens_by_user_id( $user_id ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			return [];
		}

		$table = $wpdb->prefix . "pn_notification_tokens";

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT token FROM " . $table . " WHERE user_id = %d",
				(int) $user_id,
			)
		);

		if ( empty( $results ) ) {
			return [];
		}

		return $results;
	}

	/**
	 *
	 * Get tokens by email
	 *
	 * @param $email
	 *
	 * @return array|object
	 */
	public function get_tokens_by_user_email( $email ) {

		$user = get_user_by( 'email', $email );

		if ( $user ) {
			return $this->get_tokens_by_user_id( $user->ID );
		}

		return [];
	}

	/**
	 *
	 * Get tokens by ids
	 *
	 * @param array $ids
	 *
	 * @return array|object
	 */
	public function get_tokens_by_user_ids( array $ids ) {
		global $wpdb;

		if ( empty( $ids ) || count( $ids ) == 0 ) {
			return [];
		}

		$table = $wpdb->prefix . "pn_notification_tokens";

		$results = $wpdb->get_results( "SELECT token FROM " . $table . " WHERE user_id IN(" . implode( ',', $ids ) . ")" );

		if ( empty( $results ) ) {
			return [];
		}

		return $results;
	}
}
