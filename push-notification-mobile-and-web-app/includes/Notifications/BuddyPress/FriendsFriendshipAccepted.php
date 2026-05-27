<?php

namespace PushNotify\Notifications\BuddyPress;

use PushNotify\Notifications\Notification;

/**
 * Represents a notification for when a message is sent in BuddyPress.
 */
class FriendsFriendshipAccepted extends Notification {
	/**
	 * The unique ID of the friendship.
	 *
	 * @var int
	 */
	private int $friendship_id;

	/**
	 * The friendship initiator user ID.
	 *
	 * @var int
	 */
	private int $initiator_id;

	/**
	 * The friendship request receiver user ID.
	 *
	 * @var int
	 */
	private int $friend_id;

	/**
	 * Set the friendship id.
	 *
	 * @param int $friendship_id The friendship id.
	 *
	 * @return self
	 */
	public function setFriendshipId( int $friendship_id ): self {
		$this->friendship_id = $friendship_id;
		return $this;
	}

	/**
	 * Set the initiator id.
	 *
	 * @param int $initiator_id The initiator id.
	 *
	 * @return self
	 */
	public function setInitiatorId( int $initiator_id ): self {
		$this->initiator_id = $initiator_id;
		return $this;
	}

	/**
	 * Set the friend id.
	 *
	 * @param int $friend_id The friend id.
	 *
	 * @return self
	 */
	public function setFriendId( int $friend_id ): self {
		$this->friend_id = $friend_id;
		return $this;
	}

	/**
	 * Construct the notification.
	 */
	public function __construct() {
		$this->name  = $this->trans( 'Friend accepted a friendship request' );
		$this->group = 'buddypress';
		$this->type  = 'friends_friendship_accepted';

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
		foreach ( $recipients as $value ) {
			$recipient = $value;
			if ( 'email_tag' === $recipient['type'] && '{friend_id}' === $recipient['value'] ) {
				$recipient = array(
					'type'  => 'users',
					'value' => array(
						array(
							'key' => $this->friend_id,
						),
					),
				);
			}
			if ( 'email_tag' === $recipient['type'] && '{initiator_id}' === $recipient['value'] ) {
				$recipient = array(
					'type'  => 'users',
					'value' => array(
						array(
							'key' => $this->initiator_id,
						),
					),
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

		$message = $str;
		foreach ( $this->mergeTags() as $search => $value ) {
			switch ( $value ) {
				case 'friendship_id':
					$message = str_replace( '{' . $search . '}', $this->friendship_id, $message );
					break;
				case 'initiator_id':
					$message = str_replace( '{' . $search . '}', $this->initiator_id, $message );
					break;
				case 'friend_id':
					$message = str_replace( '{' . $search . '}', $this->friend_id, $message );
					break;
				case 'friendship_initiator_name':
					$message = str_replace( '{' . $search . '}', bp_core_get_user_displayname( $this->initiator_id ), $message );
					break;
				case 'friend_name':
					$message = str_replace( '{' . $search . '}', bp_core_get_user_displayname( $this->friend_id ), $message );
					break;
				default:
					break;
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
			'friendship_id'             => 'friendship_id',
			'initiator_id'              => 'initiator_id',
			'friend_id'                 => 'friend_id',
			'friendship_initiator_name' => 'friendship_initiator_name',
			'friend_name'               => 'friend_name',
		);
	}
}
