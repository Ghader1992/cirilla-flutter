<?php

/**
 * Class NotificationJob
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 */

namespace PushNotify\Job;

use PushNotify\Message\Message;
use PushNotify\Send\Send;
use PushNotify\Job\Job;

/**
 * This class take $targets format, Message object
 * Ex:
 *
 * ```json
 *      [
 *          {"type": "registration_ids", "value": ["token_1", "token_2"] },
 *          {"type": "users", "value": [{"key": 18 }, {"key": 19 }, ] },
 *          {"type": "roles", "value": [{"key": "administrator", "text": "Administrator" } ] },
 *          {"type": "email_tag", "value": "{email}",
 *          {"type": "topic", "value": "android"
 *      ]
 * ```
 */
class NotificationJob extends Job {

	/**
	 * We pass the targets to jobs because in same case, need take times to prepare registration device's
	 * Ex: get tokes by user ids, or get tokens by roles
	 *
	 * @var array
	 */
	public array $targets;

	/**
	 *
	 * @var Message
	 */
	public Message $message;

	/**
	 * NotificationJob constructor.
	 *
	 * @param array   $targets array of targets
	 * @param Message $message Message object
	 */
	public function __construct( array $targets, Message $message ) {
		$this->targets = $targets;
		$this->message = $message;
	}

	/**
	 * Handle job logic send notification for targets
	 */
	public function handle() {
		if ( count( $this->targets ) > 0 ) {
			$send_class = pushNotify()->send();

			$send = new $send_class( $this->targets, $this->message );
			$send->push();
		}
	}
}
