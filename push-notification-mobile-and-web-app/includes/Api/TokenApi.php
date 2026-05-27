<?php

/**
 * class User
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 *
 */

namespace PushNotify\Api;

use PushNotify\Database\Token;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class TokenApi extends Base {

	/**
	 * @var Token Object with Database helper
	 */
	protected Token $tokenDb;

	public function __construct() {
		$this->tokenDb   = new Token();
		$this->namespace = constant( 'PUSH_NOTIFY_DOMAIN' ) . '/v1';
	}

	/**
	 * Add the endpoints to the API
	 * @since 1.2.0
	 * @author ngocdt
	 */
	public function register_routes() {

		/**
		 * @since 1.2.0
		 */
		register_rest_route( $this->namespace, 'get-user-tokens', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_user_tokens' ),
			'permission_callback' => '__return_true',
		) );

		/**
		 * @since 1.2.0
		 */
		register_rest_route( $this->namespace, 'update-user-token', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'update_user_token' ),
			'permission_callback' => '__return_true',
		) );

		/**
		 * @since 1.2.0
		 */
		register_rest_route( $this->namespace, 'remove-user-token', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'remove_user_token' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 *
	 * Get tokens by user id
	 *
	 * @param $request
	 *
	 * @return array|WP_Error
	 */
	public function get_user_tokens( $request ) {
		$user_id = (int) $request->get_param( 'user_id' );

		if ( ! $user_id ) {
			return new WP_Error(
				'get_user_token',
				$this->trans( 'User ID not provider.' ),
				array(
					'status' => 400,
				)
			);
		}

		return $this->tokenDb->get_tokens( $user_id );
	}

	/**
	 *
	 * Update device token for user in the database
	 * This API return success.
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_HTTP_Response
	 */
	public function update_user_token( $request ) {
		$user_id = get_current_user_id();
		$token   = $request->get_param( 'token' );

		if ( $user_id == 0 || ! $token ) {
			$error = array(
				'message' => $this->trans( 'User id or token not exist.' )
			);

			return rest_ensure_response( $error );
		}

		$result = $this->tokenDb->add_token( $user_id, $token );

		$response = array(
			'message' => $result,
		);

		return rest_ensure_response( $response );
	}

	/**
	 *
	 * Remove user token by user id and token key
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_HTTP_Response
	 */
	public function remove_user_token( $request ) {

		$user_id = (int) $request->get_param( 'user_id' );
		$token   = $request->get_param( 'token' );

		if ( ! $user_id || ! $token ) {
			return new WP_Error(
				'remove_user_token',
				$this->trans( 'User ID or token not provider.' ),
				array(
					'status' => 400,
				)
			);
		}

		$result = $this->tokenDb->remove_token( $user_id, $token );

		if ( ! $result ) {
			return new WP_Error(
				'remove_user_token',
				$this->trans( 'Remove token error.' ),
				array(
					'status' => 400,
				)
			);
		}

		$response = array(
			'message' => $this->trans( 'Remove successfully.' ),
		);

		return rest_ensure_response( $response );

	}
}
