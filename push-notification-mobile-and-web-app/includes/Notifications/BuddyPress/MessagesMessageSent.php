<?php

namespace PushNotify\Notifications\BuddyPress;

use PushNotify\Notifications\Notification;

/**
 * Represents a notification for when a message is sent in BuddyPress.
 */
class MessagesMessageSent extends Notification {
	/**
	 * The message object.
	 *
	 * @var object
	 */
	private object $message;

	/**
	 * Set the message object.
	 *
	 * @param object $message The message object.
	 *
	 * @return MessagesMessageSent
	 */
	public function setMessage( object $message ): MessagesMessageSent {
		$this->message = $message;
		return $this;
	}

	/**
	 * Construct the notification.
	 */
	public function __construct() {
		$this->name  = $this->trans( 'Messages Message Sent' );
		$this->group = 'buddypress';
		$this->type  = 'messages_message_sent';

		$this->default_title      = $this->trans( 'A new post saved' );
		$this->default_body       = $this->trans( 'Post saved' );
		$this->default_recipients = array(
			array(
				'key'   => '123456',
				'type'  => 'registration_ids',
				'value' => array(),
			),
		);

		$this->default_info = array_merge(
			$this->getDefaultBaseInfo(),
			array(),
		);
	}

	/**
	 * Validate the notification.
	 *
	 * @return bool Whether the notification is valid.
	 */
	public function validate(): bool {
		return true;
	}

	/**
	 * Prepare the recipients for the notification.
	 *
	 * @param mixed $recipients The recipients to prepare.
	 *
	 * @return array The prepared recipients.
	 */
	public function prepare_recipients( $recipients ): array {
		$data = array();
		$msg  = $this->message;
		foreach ( $recipients as $value ) {
			$recipient = $value;
			if ( 'email_tag' === $recipient['type'] && '{recipients}' === $recipient['value'] ) {

				$value = array();
				foreach ( $msg->recipients as $recipient ) {
					$value[] = array(
						'key' => $recipient->user_id,
					);
				}

				$recipient = array(
					'type'  => 'users',
					'value' => $value,
				);
			}

			$data[] = $recipient;
		}

		return $data;
	}

	/**
	 * Replaces tags in the given string with their corresponding values.
	 *
	 * @param string|null $str The string to replace tags in.
	 *
	 * @return string The string with tags replaced.
	 */
	public function replaceTags( ?string $str ): string {
		if ( empty( $str ) ) {
			return '';
		}

		if ( ! is_string( $str ) ) {
			return $str;
		}

		$msg     = $this->message;
		$message = $str;
		foreach ( $this->mergeTags() as $search => $value ) {
			if ( 'recipients' === $value ) {
				$recipients = array();
				foreach ( $msg->recipients as $recipient ) {
					$recipients[] = $recipient->user_id;
				}
				$message = str_replace( '{' . $search . '}', implode( ', ', $recipients ), $message );
			} else {
				$message = str_replace( '{' . $search . '}', $msg->$value, $message );
			}
		}

		return $message;
	}

	/**
	 * Merge tags for the notification.
	 *
	 * @return array The merged tags.
	 */
	public function mergeTags(): array {
		return array(
			'id'         => 'id',
			'thread_id'  => 'thread_id',
			'sender_id'  => 'sender_id',
			'subject'    => 'subject',
			'message'    => 'message',
			'date_sent'  => 'date_sent',
			'recipients' => 'recipients',
		);
	}
}
