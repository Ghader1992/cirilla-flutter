<?php

/**
 * Base API
 *
 * @link       https://appcheap.io
 * @since      1.0.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 */

namespace PushNotify\Api;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;

class Base extends WP_REST_Controller {

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function admin_permissions_check( WP_REST_Request $request ): bool {
		return current_user_can( constant( 'PUSH_NOTIFY_CAPABILITY' ) );
	}

	/**
	 * Check if a given request has access to read a customer.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 *
	 * @since 1.2.0
	 */
	public function user_item_permissions_check( WP_REST_Request $request ) {
		$id = $request->get_param( 'user_id' );

		if ( ! $id || get_current_user_id() != (int) $id ) {
			return new WP_Error(
				'user_item_permissions_check',
				$this->trans('Sorry, you cannot change info.'),
				array( 'status' => rest_authorization_required_code() ),
			);
		}

		return true;
	}

	/**
	 * Check user logged in
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return boolean
	 *
	 * @since 1.2.0
	 */
	public function user_logged( WP_REST_Request $request ): bool {
		return get_current_user_id() > 0;
	}

	/**
	 * Translate function
	 *
	 * @param string $text
	 *
	 * @return string|void
	 */
	public function trans( string $text ) {
		return __( $text, constant( 'PUSH_NOTIFY_DOMAIN' ) );
	}

	/**
	 * API Key permission check using App Builder's API keys
	 *
	 * @param WP_REST_Request $request
	 * @return bool|WP_Error
	 */
	public function api_key_permission_check( WP_REST_Request $request ) {
		return AppBuilderKey::instance()->api_key_permission_check( $request );
	}
}
