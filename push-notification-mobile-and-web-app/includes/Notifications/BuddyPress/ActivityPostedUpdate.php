<?php

namespace PushNotify\Notifications\BuddyPress;

use PushNotify\Notifications\Notification;

/**
 * Represents a notification for when a message is sent in BuddyPress.
 */
class ActivityPostedUpdate extends Notification {

	/**
	 * The content activity.
	 *
	 * @var string
	 */
	private string $content;

	/**
	 * The user id activity.
	 *
	 * @var string
	 */
	private int $user_id;

	/**
	 * The id activity.
	 *
	 * @var string
	 */
	private int $activity_id;


	/**
	 * Set content activity
	 *
	 * @param string $content The activity content.
	 *
	 * @return self
	 */
	public function setContent( string $content ): self {
		$this->content = $content;
		return $this;
	}

	/**
	 * Set user id activity
	 *
	 * @param int $user_id The user id activity.
	 *
	 * @return self
	 */
	public function setUserId( int $user_id ): self {
		$this->user_id = $user_id;
		return $this;
	}

	/**
	 * Set activity id
	 *
	 * @param int $activity_id The activity id.
	 *
	 * @return self
	 */
	public function setActivityId( int $activity_id ): self {
		$this->activity_id = $activity_id;
		return $this;
	}


	/**
	 * Construct the notification.
	 */
	public function __construct() {
		$this->name  = $this->trans( 'Activity Posted Update' );
		$this->group = 'buddypress';
		$this->type  = 'bp_activity_posted_update';

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
			if ( 'email_tag' === $recipient['type'] && '{user_id}' === $recipient['value'] ) {
				$recipient = array(
					'type'  => 'users',
					'value' => array(
						array( 'key' => $this->user_id ),
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
				case 'content':
					$message = str_replace( '{' . $search . '}', $this->content, $message );
					break;
				case 'user_id':
					$message = str_replace( '{' . $search . '}', $this->user_id, $message );
					break;
				case 'activity_id':
					$message = str_replace( '{' . $search . '}', $this->activity_id, $message );
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
			'content'     => 'content',
			'user_id'     => 'user_id',
			'activity_id' => 'activity_id',
		);
	}
}
