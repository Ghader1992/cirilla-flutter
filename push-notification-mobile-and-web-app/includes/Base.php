<?php

namespace PushNotify;

defined( 'ABSPATH' ) || exit;

class Base {
	public function trans( string $text ) {
		return __( $text, constant( 'PUSH_NOTIFY_DOMAIN' ) );
	}
}