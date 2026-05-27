<?php

/**
 * class UserNotification
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 *
 */

namespace PushNotify\Api;

use PushNotify\Database\Notification;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class NotificationApi extends Base {

	protected Notification $notificationDb;
	protected $domain;

	public function __construct() {
		$this->domain         = defined( 'PUSH_NOTIFY_DOMAIN' ) ? PUSH_NOTIFY_DOMAIN : 'push-notify';
		$this->namespace      = $this->domain . '/v1';
		$this->notificationDb = new Notification();
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
		register_rest_route( $this->namespace, 'notifications', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_user_notifications' ),
			'permission_callback' => array( $this, 'user_item_permissions_check' ),
		) );

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id'                 => array(
						'description' => __( 'Unique identifier for the resource.', $this->domain ),
						'type'        => 'integer',
						'required'    => true,
					),
					'user_id'            => array(
						'description' => __( 'User id for the resource.', $this->domain ),
						'type'        => 'integer',
						'required'    => true,
					),
					'app-builder-decode' => array(
						'description' => __( 'Auth param', $this->domain ),
						'type'        => 'boolean',
						'required'    => true,
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_user_notification' ),
					'permission_callback' => array( $this, 'user_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'remove_user_notification' ),
					'permission_callback' => array( $this, 'user_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route( $this->namespace, 'unread', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'unread' ),
			'permission_callback' => array( $this, 'user_logged' ),
		) );

		register_rest_route( $this->namespace, 'read', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'read' ),
			'permission_callback' => array( $this, 'user_logged' ),
		) );

		register_rest_route( $this->namespace, 'delete', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete' ),
			'permission_callback' => array( $this, 'user_logged' ),
		) );
	}

	/**
	 *
	 * Get user notification by id
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function get_user_notifications( $request ) {

		$user_id  = $request->get_param( 'user_id' );
		$page     = $request->get_param( 'page' ) ? (int) $request->get_param( 'page' ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? (int) $request->get_param( 'per_page' ) : 10;

		if ( ! $user_id ) {
			return new WP_Error(
				'get_user_notifications',
				__( 'User id not found!', $this->domain ),
				array(
					'status' => 400,
				)
			);
		}

		$results = $this->notificationDb->get_notifications( (int) $user_id, $page, $per_page );

		$notifications = array();

		foreach ( $results as $notification ) {
			$notifications[] = $this->pre_item_response( $notification );
		}

		return rest_ensure_response( $notifications );
	}

	/**
	 *
	 * Retrieve a notification
	 *
	 * @param $request
	 *
	 * @return mixed|WP_Error
	 */
	public function get_user_notification( $request ) {
		$id = $request->get_param( 'id' );

		if ( ! $id ) {
			return new WP_Error(
				'get_user_notifications',
				__( 'Notification id not found!', $this->domain ),
				array(
					'status' => 400,
				)
			);
		}

		$results = $this->notificationDb->get_notification( (int) $id );

		if ( count( $results ) <= 0 ) {
			return new WP_Error(
				'get_user_notification',
				__( 'Notification not found in the database!', $this->domain ),
				array(
					'status' => 400,
				)
			);
		}

		return $this->pre_item_response( $results[0] );
	}

	/**
	 *
	 * Delete the notification.
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function remove_user_notification( $request ) {
		$id = $request->get_param( 'id' );

		if ( ! $id ) {
			return new WP_Error(
				'remove_user_notification',
				__( 'Notification id not found!', $this->domain ),
				array(
					'status' => 400,
				)
			);
		}

		$result = $this->notificationDb->remove_notification( (int) $id );

		if ( ! $result ) {
			return new WP_Error(
				'remove_user_notification',
				__( 'Something wrong when delete the notification!', $this->domain ),
				array(
					'status' => 400,
				)
			);
		}

		return rest_ensure_response( array( 'message' => __( 'Delete successfully!', $this->domain ) ) );
	}

	/**
	 *
	 * Delete all notification by user_id
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function delete() {
		$user_id = get_current_user_id();

		$seen = $this->notificationDb->remove_notifications( $user_id );

		return rest_ensure_response(
			array( 'deleted' => $seen ),
		);
	}

	/**
	 *
	 * Get total message unread
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function unread() {
		$user_id = get_current_user_id();

		$countUnRead = $this->notificationDb->un_read_notification( $user_id );

		return rest_ensure_response(
			array( 'count' => $countUnRead ),
		);
	}

	/**
	 *
	 * Update status notification
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function read( $request ) {

		$id = $request->get_param( 'id' );

		if ( ! $id ) {
			return new WP_Error(
				'remove_user_notification',
				__( 'Notification id not found!', $this->domain ),
				array(
					'status' => 400,
				)
			);
		}

		$user_id = get_current_user_id();

		$update = $this->notificationDb->read_notification( (int) $id, $user_id );

		return rest_ensure_response(
			array( 'update' => $update ),
		);
	}

	/**
	 *
	 * Prepare item notification
	 *
	 * @param $item
	 *
	 * @return mixed
	 */
	public function pre_item_response( $item ) {

		$item->payload = maybe_unserialize( $item->payload );
		$item->action  = maybe_unserialize( $item->action );

		return $item;
	}

	/**
	 * @return array
	 */
	public function get_public_item_schema(): array {
		return array();
	}
}
