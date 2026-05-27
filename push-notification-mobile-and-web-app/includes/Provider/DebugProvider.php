<?php

/**
 * class DebugProvider
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 */

namespace PushNotify\Provider;

use PushNotify\Message\Message;

class DebugProvider implements ProviderInterface {

	public function get_method() {
		return 'debug';
	}

	public function send( Message $message ): array {
		error_log( json_encode( $message->toFcmV1() ) );
		return array();
	}
}
