<?php

/**
 * Class DebugProvider
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 * @package PushNotify\Provider
 */

namespace PushNotify\Provider;

use PushNotify\Message\Message;

/**
 * Class DebugProvider
 */
class SignalProvider implements ProviderInterface {

	/**
	 * OneSignal API URL
	 *
	 * @var string
	 */
	public string $onesignal_api_url = 'https://onesignal.com/api/v1/notifications';

    public function get_method() {
        return 'signal';
    }

	/**
	 * Send notification
	 *
	 * @param Message $message Message object.
	 *
	 * @return array
	 */
	public function send( Message $message ): array {

		$onesignal_app_id       = pushNotify()->settings()->get( 'onesignal_app_id' );
		$onesignal_rest_api_key = pushNotify()->settings()->get( 'onesignal_rest_api_key' );

		if ( empty( $onesignal_app_id ) || empty( $onesignal_rest_api_key ) ) {
			return array();
		}

		$request_data = array(
			'body'    => wp_json_encode( $message->to_one_signal_body_message( $onesignal_app_id ) ),
			'headers' => array(
				'timeout'       => 45,
				'redirection'   => 5,
				'httpversion'   => '1.1',
				'method'        => 'POST',
				'Authorization' => 'Bearer ' . $onesignal_rest_api_key,
				'accept'        => 'application/json',
				'Content-Type'  => 'application/json',
				'cookies'       => array(),
			),
		);

		// Send onesignal notification.
		$response = wp_remote_post( $this->onesignal_api_url, $request_data );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$response_body = wp_remote_retrieve_body( $response );
		return array();
	}
}
