<?php

namespace PushNotify\Notifications\BuddyPress;

use PushNotify\Notifications\Notification;

/**
 * Represents a notification for when a message is sent in BuddyPress.
 * $group_id,$invited_users,$inviter_id
 */
class GroupsSendInvites extends Notification {

	/**
	 * ID of the group who's being invited to.
	 *
	 * @var int
	 */
	private int $group_id;

	/**
	 * Array of users being invited to the group.
	 *
	 * @var array
	 */
	private int $invited_users;

	/**
	 * ID of the inviting user.
	 *
	 * @var int
	 */
	private int $inviter_id;

	/**
	 * Get the group id.
	 *
	 * @return int
	 */
	public function getGroupId(): int {
		return $this->group_id;
	}

	/**
	 * Get the invited users.
	 *
	 * @return array
	 */
	public function getInvitedUsers(): array {
		return $this->invited_users;
	}

	/**
	 * Get the inviter id.
	 *
	 * @return int
	 */
	public function getInviterId(): int {
		return $this->inviter_id;
	}

	/**
	 * Set the group id.
	 *
	 * @param int $group_id The group id.
	 *
	 * @return self
	 */
	public function setGroupId( int $group_id ): self {
		$this->group_id = $group_id;
		return $this;
	}

	/**
	 * Set the invited users.
	 *
	 * @param array $invited_users The invited users.
	 *
	 * @return self
	 */
	public function setInvitedUsers( array $invited_users ): self {
		$this->invited_users = $invited_users;
		return $this;
	}

	/**
	 * Set the inviter id.
	 *
	 * @param int $inviter_id The inviter id.
	 *
	 * @return self
	 */
	public function setInviterId( int $inviter_id ): self {
		$this->inviter_id = $inviter_id;
		return $this;
	}

	/**
	 * Construct the notification.
	 */
	public function __construct() {
		$this->name  = $this->trans( 'Groups Send Invites' );
		$this->group = 'buddypress';
		$this->type  = 'groups_send_invites';

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

			/** Invited users */
			if ( 'email_tag' === $recipient['type'] && '{invited_users}' === $recipient['value'] ) {
				$value = array();
				foreach ( $this->invited_users as $user_id ) {
					$value[] = array(
						'key' => $user_id,
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

		$message = $str;
		foreach ( $this->mergeTags() as $search => $value ) {
			switch ( $value ) {
				case 'group_id':
					$message = str_replace( '{' . $search . '}', $this->group_id, $message );
					break;
				case 'invited_users':
					$message = str_replace( '{' . $search . '}', wp_json_encode( $this->invited_users ), $message );
					break;
				case 'inviter_id':
					$message = str_replace( '{' . $search . '}', $this->inviter_id, $message );
					break;
				case 'group_name':
					$message = str_replace( '{' . $search . '}', bp_get_group_name( groups_get_group( $this->group_id ) ), $message );
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
			'group_id'      => 'group_id',
			'invited_users' => 'invited_users',
			'inviter_id'    => 'inviter_id',
			'group_name'    => 'group_name',
		);
	}
}
