<?php

namespace PushNotify\Notifications\Wc;

use PushNotify\Notifications\Notification;
use WP_Post;

/**
 * Notification post change status
 */
class ProductStatusChanged extends Notification {

	private WP_Post $post;
	private string $old_status;
	private string $new_status;

	public function __construct() {
		$this->name  = $this->trans( 'New product' );
		$this->group = 'wc';
		$this->type  = 'product_status_changed';

		$this->default_title      = $this->trans( 'New product' );
		$this->default_body       = $this->trans( 'The {name} publish' );
		$this->default_recipients = [
			[ 'key' => '123456', 'type' => 'registration_ids', 'value' => [] ],
		];

		$this->default_info = array_merge(
			$this->getDefaultBaseInfo(), [],
		);
	}

	/**
	 * Set post ID
	 *
	 * @param string $new_status
	 * @param string $old_status
	 *
	 * @param WP_Post $post
	 *
	 * @return ProductStatusChanged
	 */
	public function setPost( string $new_status, string $old_status, WP_Post $post ): ProductStatusChanged {
		$this->post       = $post;
		$this->new_status = $new_status;
		$this->old_status = $old_status;

		return $this;
	}

	/**
	 * Get merge tags keys
	 *
	 * @return array
	 */
	public function mergeTags(): array {
		return [
			'id'               => 'ID',
			'description'      => 'post_content',
			'name'             => 'post_title',
			'sort_description' => 'post_excerpt',
			'old_status'       => 'old_status',
			'new_status'       => 'new_status',
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

		$post    = $this->post;
		$message = $str;
		foreach ( $this->mergeTags() as $search => $value ) {
			$replace = $post->$value ?? '';

			if ( $search == 'old_status' ) {
				$replace = $this->old_status;
			}

			if ( $search == 'new_status' ) {
				$replace = $this->new_status;
			}

			$message = str_replace( '{' . $search . '}', $replace, $message );
		}

		return $message;
	}

	public function validate(): bool {
		return isset( $this->post );
	}
}

