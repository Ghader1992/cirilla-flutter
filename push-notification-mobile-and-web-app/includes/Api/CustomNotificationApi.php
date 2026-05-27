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

use PushNotify\Job\NotificationJob;
use PushNotify\Message\Message;
use WP_Error;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class CustomNotificationApi extends Base {
	public function __construct() {
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
		register_rest_route( $this->namespace, 'send-notification', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'send_notification' ),
			'permission_callback' => array( $this, 'admin_permissions_check' ),
		) );
	}

	public function send_notification( $request ) {

		// Message
		$title = $request->get_param( 'title' );
		$body  = $request->get_param( 'body' );
		$image = $request->get_param( 'image' );
		$sound = $request->get_param( 'sound' );

		// Target
		$recipients = $request->get_param( 'recipients' );

		// Action
		$type   = $request->get_param( 'type' );
		$router = $request->get_param( 'router' );
		$args   = $request->get_param( 'action' );

		/**
		 * validate message
		 */
		if ( ! $title || ! $body ) {
			return new WP_Error(
				'send_notification',
				$this->trans( 'The title or body message not provider!' ),
				array(
					'status' => 400,
				)
			);
		}

		/**
		 * Validate target
		 */
		if ( ! $recipients || ! is_array( $recipients ) || count( $recipients ) == 0 ) {
			return new WP_Error(
				'send_notification',
				$this->trans( 'Targets not provider!' ),
				array(
					'status' => 400,
				)
			);
		}

		$message = new Message( $title, $body );

		if ( ! empty( $image ) ) {
			$message->setImage( $image );
		}

		if ( ! empty( $sound ) && $sound != 'disabled') {
			$message->setSound( $sound );
		}

		$data = [];
		if ( ! empty( $type ) ) {
			$data['type'] = $type;
		}
		if ( ! empty( $router ) ) {
			$data['route'] = $router;
		}
		if ( ! empty( $args ) ) {
			$data['args'] = $args;
		}
		$message->setData( $data );

		pushNotify()->push( new NotificationJob( $recipients, $message ) );

		return rest_ensure_response( [] );
	}
}
