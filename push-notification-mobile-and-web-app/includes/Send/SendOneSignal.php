<?php

/**
 * Class Send
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 * @package PushNotify\Send
 */

namespace PushNotify\Send;

use PushNotify\Database\Notification;
use PushNotify\Database\Token;
use PushNotify\Functions;
use PushNotify\Job\TokenJob;
use PushNotify\Message\Message;

/**
 * Class Send
 *
 * This class is responsible for sending push notifications to different types of targets, such as registration IDs, users, roles, topics, and email tags.
 *
 * @package PushNotify\Send
 */
class SendOneSignal implements SendInterface {

	/**
	 * We pass the targets to jobs because in same case, need take times to prepare registration device's
	 * Ex: get tokes by user ids, or get tokens by roles
	 *
	 * @var array
	 */
	public array $targets;

	/**
	 * The message to be sent.
	 *
	 * @var Message
	 */
	public Message $message;

	/**
	 * Send constructor.
	 *
	 * @param array   $targets The targets to which the message will be sent.
	 * @param Message $message The message to be sent.
	 */
	public function __construct( array $targets, Message $message ) {
		$this->targets = $targets;
		$this->message = $message;
	}

	/**
	 * Push message
	 */
	public function push(): void {
		foreach ( $this->targets as $value ) {
			switch ( $value['type'] ) {
				case 'registration_ids':
					if ( is_array( $value['value'] ) ) {
						$this->push_to_registration_ids( $value['value'] );
					}
					break;
				case 'users':
					if ( is_array( $value['value'] ) ) {
						$this->push_to_users( $value['value'] );
					}
					break;
				case 'roles':
					if ( is_array( $value['value'] ) ) {
						$this->push_to_roles( $value['value'] );
					}
					break;
				case 'topic':
					if ( Functions::is_valid_json( $value['value'] ) ) {
						$topics = json_decode( $value['value'], true );
						foreach ( $topics as $topic ) {
							$this->push_to_topic( $topic );
						}
					} else {
						$this->push_to_topic( $value['value'] );
					}
					break;
				case 'email_tag':
					if ( is_email( $value['value'] ) ) {
						$this->push_to_email( $value['value'] );
					}
					break;
				default:
					error_log( 'push_to_' . $value['type'] );
			}
		}
	}

	/**
	 * Push notification to list registration_ids
	 *
	 * @param array $tokens The registration IDs of the intended recipients. Ex: ["token_1", "token_2"] or {"key1: "token_1", "key_2": "token_2"}.
	 */
	public function push_to_registration_ids( array $tokens ): void {
		$message          = $this->message;
		$registration_ids = array();
		foreach ( $tokens as $token ) {
			$registration_ids[] = $token;
		}

		if ( count( $registration_ids ) > 0 ) {
			$message->setRegistrationIds( $registration_ids );
			pushNotify()->notification()->send( $message );
		}
	}

	/**
	 * Push notification to list user
	 *
	 * @param array $users The user IDs of the intended recipients Ex: [{"key": 18 }, {"key": 19 }].
	 */
	public function push_to_users( $users ): void {
		$message = $this->message;
		$fiters  = array();

		foreach ( $users as $user ) {
			$user_id = (int) $user['key'];
			if ( $user_id > 0 ) {
				$fiters[] = array(
					'field'    => 'tag',
					'key'      => 'user_id',
					'relation' => '=',
					'value'    => $user_id,
				);
				$fiters[] = array(
					'operator' => 'OR',
				);
			}
		}

		if ( count( $fiters ) > 0 ) {

			// Remove last operator OR.
			array_pop( $fiters );

			$message->set_filters( $fiters );
			pushNotify()->notification()->send( $message );
		}
	}

	/**
	 * Push notifications to roles
	 *
	 * @param array $roles The roles of the intended recipients.
	 */
	public function push_to_roles( array $roles ): void {
		$message = $this->message;
		$fiters  = array();
		if ( ! empty( $roles ) && count( $roles ) > 0 ) {
			foreach ( $roles as $value ) {
				if ( isset( $value['key'] ) ) {
					$fiters[] = array(
						'field'    => 'tag',
						'key'      => $value['key'],
						'relation' => '=',
						'value'    => 1,
					);
					$fiters[] = array(
						'operator' => 'OR',
					);
				}
			}
		}

		if ( count( $fiters ) > 0 ) {

			// Remove last operator OR.
			array_pop( $fiters );

			$message->set_filters( $fiters );
			pushNotify()->notification()->send( $message );
		}
	}

	/**
	 * Push notification to topic
	 *
	 * @param string $topic The topic to which the recipients are subscribed.
	 */
	public function push_to_topic( string $topic ): void {

		if ( empty( $topic ) ) {
			return;
		}

		$message = $this->message;
		$fiters  = array();

		$fiters[] = array(
			'field'    => 'tag',
			'key'      => 'topic',
			'relation' => '=',
			'value'    => $topic,
		);

		$message->set_filters( $fiters );
		pushNotify()->notification()->send( $message );
	}

	/**
	 * Push notification to email
	 *
	 * @param string $email The email of the intended recipient.
	 */
	public function push_to_email( $email ): void {

		if ( empty( $email ) ) {
			return;
		}

		$message = $this->message;
		$fiters  = array();

		$fiters[] = array(
			'field'    => 'tag',
			'key'      => 'email',
			'relation' => '=',
			'value'    => $email,
		);

		$message->set_filters( $fiters );
		pushNotify()->notification()->send( $message );
	}
}
