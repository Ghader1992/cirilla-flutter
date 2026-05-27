<?php

namespace PushNotify\Notifications\Wcfm;

use PushNotify\Notifications\Notification;

/**
 * AfterWcfmNotification
 */
class AfterWcfmNotification extends Notification {

	private $author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, $wcfm_messages_type = 'direct', $email_notification = true;

	public function __construct() {
		$this->name  = $this->trans( 'After Wcfm Notification' );
		$this->group = 'wcfm';
		$this->type  = 'after_wcfm_notification';

		$this->default_title      = $this->trans( 'Notification title' );
		$this->default_body       = $this->trans( 'Notification body' );
		$this->default_recipients = [
			[ 'key' => '123456', 'type' => 'registration_ids', 'value' => [] ],
		];

		$this->default_info = array_merge(
			$this->getDefaultBaseInfo(), [],
		);
	}

	public function setMessage( $author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, $wcfm_messages_type = 'direct' ): AfterWcfmNotification {
		$this->author_id          = $author_id;
		$this->message_to         = $message_to;
		$this->author_is_admin    = $author_is_admin;
		$this->author_is_vendor   = $author_is_vendor;
		$this->wcfm_messages      = $wcfm_messages;
		$this->wcfm_messages_type = $wcfm_messages_type;

		return $this;
	}

	/**
	 * Get merge tags keys
	 *
	 * @return array
	 */
	public function mergeTags(): array {
		return [
			'author_id'          => 'author_id',
			'message_to'         => 'message_to',
			'author_is_admin'    => 'author_is_admin',
			'author_is_vendor'   => 'author_is_vendor',
			'wcfm_messages'      => 'wcfm_messages',
			'wcfm_messages_type' => 'wcfm_messages_type',
		];
	}

	/**
	 * Return the data after replace
	 *
	 * @param string|null $str
	 *
	 * @return string
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
			if ( property_exists( $this, $search ) ) {
				$message = str_replace( '{' . $search . '}', $this->{$value}, $message );
			}
		}

		return $message;
	}

	/**
	 * Prepare merge tag
	 *
	 * @param $recipients
	 *
	 * @return array
	 */
	public function prepare_recipients( $recipients ): array {
		$data = [];

		foreach ( $recipients as $value ) {
			$recipient = $value;
			if ( $recipient['type'] == 'email_tag' ) {
				switch ( $recipient['tag'] ) {
					case '{author_id}':
						$recipient['type']  = 'users';
						$recipient['value'] = [
							[ 'key' => $this->author_id ],
						];
						break;
					case '{message_to}':
						$recipient['type']  = 'users';
						$recipient['value'] = [
							[ 'key' => $this->message_to ],
						];
						break;
					default:
						break;
				}
			}

			$data[] = $recipient;
		}

		return $data;
	}

	public function validate(): bool {
		return true;
	}
}

