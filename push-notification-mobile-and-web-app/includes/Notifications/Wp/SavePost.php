<?php

namespace PushNotify\Notifications\Wp;

use PushNotify\Notifications\Notification;
use WP_Post;

/**
 * Notification save post
 */
class SavePost extends Notification {

	/**
	 * @var int post id
	 */
	private int $post_id;
	private WP_Post $post;
	private bool $update;

	public function __construct() {
		$this->name  = $this->trans( 'Save Post' );
		$this->group = 'wp';
		$this->type  = 'save_post';

		$this->default_title      = $this->trans( 'Post saved' );
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
	 * Set post ID
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 * @param bool    $update
	 *
	 * @return SavePost
	 */
	public function setPost( int $post_id, WP_Post $post, bool $update ): SavePost {
		$this->post_id = $post_id;
		$this->post    = $post;
		$this->update  = $update;

		return $this;
	}

	/**
	 * Get merge tags keys
	 *
	 * @return array
	 */
	public function mergeTags(): array {
		return array(
			'id'                => 'ID',
			'post_author'       => 'post_author',
			'post_date'         => 'post_date',
			'post_date_gmt'     => 'post_date_gmt',
			'post_content'      => 'post_content',
			'post_title'        => 'post_title',
			'post_excerpt'      => 'post_excerpt',
			'post_status'       => 'post_status',
			'post_type'         => 'post_type',
			'comment_status'    => 'comment_status',
			'ping_status'       => 'ping_status',
			'post_password'     => 'post_password',
			'post_name'         => 'post_name',
			'post_modified'     => 'post_modified',
			'post_modified_gmt' => 'post_modified_gmt',
			'post_parent'       => 'post_parent',
			'post_mime_type'    => 'post_mime_type',
			'menu_order'        => 'menu_order',
			'comment_count'     => 'comment_count',
			'thumbnail'         => 'thumbnail',
			'update'            => 'update',
			'categories'        => '/\{categories(?:\s+property:([a-zA-Z0-9_-]+))?(?:\s+prefix:([a-zA-Z0-9_-]+))?\}/',
		);
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

			if ( 'categories' === $search ) {
				$categories = get_the_category( $this->post_id );

				if ( ! $categories ) {
					continue;
				}

				// Has variable in the merge tag.
				$matches = Functions::get_regex_matches( $value, $message );
				if ( count( $matches ) > 0 ) {
					$property = $matches[1] ?? 'id';
					$prefix   = $matches[2] ?? '';

					$replace = array();
					foreach ( $categories as $category ) {
						if ( isset( $category->$property ) ) {
							$replace[] = $prefix . $category->$property;
						}
					}

					$message = preg_replace( $value, wp_json_encode( $replace ), $message );
					continue;
				}
			}

			$replace = $post->$value ?? '';

			if ( $search == 'update' ) {
				$replace = $this->update ? 'true' : 'false';
			}

			if ( $search == 'thumbnail' && has_post_thumbnail( $this->post_id ) ) {
				$replace = wp_get_attachment_url( get_post_thumbnail_id( $this->post_id ) );
			}

			$message = str_replace( '{' . $search . '}', $replace, $message );
		}

		return $message;
	}

	public function validate(): bool {
		return isset( $this->post_id );
	}
}
