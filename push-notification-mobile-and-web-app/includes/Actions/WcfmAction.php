<?php

/**
 * class WooAction
 *
 * @link       https://appcheap.io
 * @author     ngocdt
 * @since      1.2.0
 */

namespace PushNotify\Actions;

use PushNotify\Notifications\Wcfm\AfterWcfmNotification;

defined( 'ABSPATH' ) || exit;

class WcfmAction {

	public function __construct() {
		add_action( 'after_wcfm_notification', array( $this, 'after_wcfm_notification' ), 100, 6 );
	}

	/**
	 *
	 * Fires when after_wcfm_notification
	 *
	 * @param $author_id
	 * @param $message_to
	 * @param $author_is_admin
	 * @param $author_is_vendor
	 * @param $wcfm_messages
	 * @param string           $wcfm_messages_type
	 * @param bool             $email_notification
	 */
	public function after_wcfm_notification( $author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, $wcfm_messages_type = 'direct', $email_notification = true ) {

		$notifications = pushNotify()->settings()->notifications();

		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && $value['type'] == 'after_wcfm_notification' && $value['status'] ) {
				$obj = new AfterWcfmNotification();

				$obj->setMessage( $author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, $wcfm_messages_type )
					->setId( $key )
					->push();
			}
		}
	}
}
