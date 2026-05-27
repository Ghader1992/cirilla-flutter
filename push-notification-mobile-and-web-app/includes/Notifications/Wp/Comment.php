<?php

namespace PushNotify\Notifications\Wp;

use PushNotify\Notifications\Notification;

class Comment extends Notification {

	/**
	 * @var int comment id
	 */
	private int $comment_id;

	public function __construct() {
		$this->name  = $this->trans( 'New Comment' );
		$this->group = 'wp';
		$this->type  = 'comment';

		$this->default_title      = $this->trans( 'A new comment added' );
		$this->default_body       = $this->trans( 'A new comment added on the post "{post_title}" by {author} ({email}).' );
		$this->default_recipients = [
			[ 'key' => '123456', 'type' => 'registration_ids', 'value' => [] ],
		];

		$this->default_info = array_merge(
			$this->getDefaultBaseInfo(), [],
		);
	}

	/**
	 * Set comment ID
	 *
	 * @param int $comment_id
	 *
	 * @return self
	 */
	public function setComment( int $comment_id ): Comment {
		$this->comment_id = $comment_id;

		return $this;
	}

	/**
	 * Get merge tags keys
	 *
	 * @return array
	 */
	public function mergeTags(): array {
		return [
			'author'     => 'comment_author',
			'email'      => 'comment_author_email',
			'author_url' => 'comment_author_url',
			'comment'    => 'comment_content',
			'ip'         => 'comment_author_ip',
			'post_id'    => 'comment_post_ID',
			'post_title' => 'post_title',
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

		$comment = get_comment( $this->comment_id );
		$message = $str;
		foreach ( $this->mergeTags() as $search => $value ) {
            $replace = $comment->$value ?? '';
			$message = str_replace( '{' . $search . '}', $replace, $message );
		}

		return $message;
	}

	public function validate(): bool {
		return isset( $this->comment_id );
	}

	/**
	 * Prepare merge tag
	 *
	 * @param $recipients
	 *
	 * @return array
	 */
	public function prepare_recipients( $recipients ): array {

		$comment = get_comment( $this->comment_id );
		$data    = [];

		foreach ( $recipients as $value ) {
			$recipient = $value;
			if ( $recipient['type'] == 'email_tag' && ! is_email( $recipient['value'] ) ) {
				switch ( $recipient['value'] ) {
					case '{email}':
					case '{comment_author_email}':
						$recipient['value'] = $comment->comment_author_email;
						break;
					default:
						break;
				}
			}

			$data[] = $recipient;
		}

		return $data;
	}
}

