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

class FirebaseProviderV1 implements ProviderInterface {

	public function get_method() {
		return 'firebasev1';
	}

	public function send( Message $message ) {

		$service_account_json = pushNotify()->settings()->get( 'service_account_json' );

		if ( ! $service_account_json ) {
			return new WP_Error(
				'send_notification',
				__( 'Firebase service account json not setting yet!', constant( 'PUSH_NOTIFY_DOMAIN' ) ),
				array(
					'status' => 400,
				)
			);
		}

		$service_account = json_decode( $service_account_json, true );

		// Access token
		$access_token = $this->get_access_token( $service_account );

		$args = array(
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'method'      => 'POST',
			'body'        => json_encode(
				array(
					'message' => $message->toFcmV1(),
				)
			),
			'sslverify'   => false,
			'headers'     => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			),
			'cookies'     => array(),
		);

		$url = 'https://fcm.googleapis.com/v1/projects/{projectId}/messages:send';
		$url = str_replace( '{projectId}', $service_account['project_id'], $url );

		$response = wp_remote_post( $url, $args );

		if ( ! is_wp_error( $response ) ) {
			return json_decode( wp_remote_retrieve_body( $response ), true );
		}

		return array();
	}

	/**
	 * Get access token from service account json by REST API
	 *
	 * @param array $service_account
	 *
	 * @return string $access_token
	 */
	public function get_access_token( $service_account ) {
		$url       = 'https://oauth2.googleapis.com/token';
		$scope     = 'https://www.googleapis.com/auth/firebase.messaging';
		$cacke_key = 'pn_push_notificaiton_access_token';

		// Get access token from cache
		$access_token_cached = get_transient( $cacke_key );
		if ( $access_token_cached ) {
			return $access_token_cached;
		}

		// Read the service account key file
		$serviceAccount = $service_account;

		// Create the JWT header
		$header = array(
			'alg' => 'RS256',
			'typ' => 'JWT',
		);

		// Create the JWT claim set
		$now      = time();
		$claimSet = array(
			'iss'   => $serviceAccount['client_email'],
			'scope' => $scope,
			'aud'   => $url,
			'exp'   => $now + 3600,
			'iat'   => $now,
		);

		// Encode the header and claim set
		$base64UrlHeader   = str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), base64_encode( json_encode( $header ) ) );
		$base64UrlClaimSet = str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), base64_encode( json_encode( $claimSet ) ) );

		// Create the signature
		$signatureInput = $base64UrlHeader . '.' . $base64UrlClaimSet;
		openssl_sign( $signatureInput, $signature, $serviceAccount['private_key'], 'sha256' );
		$base64UrlSignature = str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), base64_encode( $signature ) );

		// Create the JWT
		$jwt = $base64UrlHeader . '.' . $base64UrlClaimSet . '.' . $base64UrlSignature;

		// Prepare the POST fields
		$postFields = array(
			'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
			'assertion'  => $jwt,
		);

		// Initialize cURL
		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $postFields ) );

		// Execute the request
		$result = curl_exec( $ch );
		if ( $result === false ) {
			die( 'Curl failed: ' . curl_error( $ch ) );
		}

		curl_close( $ch );

		// Decode the response
		$response = json_decode( $result, true );

		// Cache access token
		if ( isset( $response['access_token'] ) && $response['expires_in'] ) {
			$access_token = $response['access_token'];
			$expires_in   = $response['expires_in'];
			set_transient( $cacke_key, $access_token, $expires_in );
		}

		return $response['access_token'];
	}
}
