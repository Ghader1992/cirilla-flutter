<?php

/**
 * class NotificationJob
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 */

namespace PushNotify\Provider;

use PushNotify\Message\Message;
use WP_Error;

class FirebaseProvider implements ProviderInterface {

    public function get_method() {
        return 'firebase';
    }

	public function send( Message $message ) {

		$firebase_server_key = pushNotify()->settings()->get( 'firebase_server_key' );

		if ( ! $firebase_server_key ) {
			return new WP_Error(
				'send_notification',
				__( 'Firebase server key not setting yet!', constant( 'PUSH_NOTIFY_DOMAIN' ) ),
				array(
					'status' => 400,
				)
			);
		}

		$args = array(
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'method'      => 'POST',
			'body'        => json_encode( $message->toMessage() ),
			'sslverify'   => false,
			'headers'     => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'key=' . $firebase_server_key,
			),
			'cookies'     => array(),
		);

		$response = wp_remote_post( constant( 'PUSH_NOTIFY_REST_API_NOTIFICATION' ), $args );

		if ( ! is_wp_error( $response ) ) {
			return json_decode( wp_remote_retrieve_body( $response ), true );
		}

		return array();
	}
}
