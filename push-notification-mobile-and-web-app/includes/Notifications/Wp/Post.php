<?php
/**
 * Notification post change status
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 * @package    PushNotify
 */

namespace PushNotify\Notifications\Wp;

use PushNotify\Notifications\Notification;
use PushNotify\Functions;
use WP_Post;

/**
 * Notification post change status
 */
class Post extends Notification {

	/**
	 * @var int post id of the post.
	 */
	private int $post_id;

	/**
	 * @var WP_Post post object.
	 */
	private WP_Post $post;

	/**
	 * @var string old status.
	 */
	private string $old_status;

	/**
	 * Post constructor.
	 */
	public function __construct() {
		$this->name  = $this->trans( 'Post saved' );
		$this->group = 'wp';
		$this->type  = 'post';

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
	 * Set post ID
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 * @param string  $old_status
	 *
	 * @return Post
	 */
	public function setPost( int $post_id, WP_Post $post, string $old_status ): Post {
		$this->post_id    = $post_id;
		$this->post       = $post;
		$this->old_status = $old_status;

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
			'old_status'        => 'old_status',
			'categories'        => '/\{categories(?:\s+property:([a-zA-Z0-9_-]+))?(?:\s+prefix:([a-zA-Z0-9_-]+))?\}/',
			'object'            => '/\{object.(?:([a-zA-Z0-9_-]+))?(?:\s+property:([a-zA-Z0-9_-]+))?(?:\s+prefix:([a-zA-Z0-9_-]+))?\}/',
		);
	}

	/**
	 * Return the data after replace
	 *
	 * @param string|null $str The string to replace.
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
			if ( 'object' === $search ) {
				$matches = Functions::get_regex_matches( $value, $message );

				if ( count( $matches ) > 0 ) {

					$property  = $matches[1];
					$property2 = $matches[2];
					$prefix    = $matches[3];

					$property_value = $post->$property ?? get_the_terms( $post->ID, $property );

					if ( ! empty( $property2 ) && is_object( $property_value ) ) {
						$property_value = $property_value->$property2 ?? '';
					}

					if ( ! empty( $property2 ) && is_array( $property_value ) && is_numeric( $property2 ) ) {
						$property_value = $property_value[ $property2 ] ?? '';
					}

					if ( empty( $property_value ) ) {
						$message = $prefix . $property_value;
						continue;
					}

					if ( is_array( $property_value ) ) {
						$replace = array();
						foreach ( $property_value as $p ) {
							if ( is_object( $p ) ) {
								$replace[] = $prefix . $p->$property2;
							} else {
								$replace[] = $prefix . $p;
							}
						}
						$message = preg_replace( $value, wp_json_encode( $replace ), $message );
						continue;
					}
				}
			}

			if ( 'categories' === $search ) {
				$categories = get_the_category( $this->post_id );

				if ( ! $categories ) {
					continue;
				}

				// Has variable in the merge tag.
				$matches = Functions::get_regex_matches( $value, $message );
				if ( count( $matches ) > 0 ) {
					$property = $matches[1] ?? 'term_id';
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

			if ( 'old_status' === $search ) {
				$replace = $this->old_status;
			}

			if ( 'thumbnail' === $search && has_post_thumbnail( $this->post_id ) ) {
				$replace = wp_get_attachment_url( get_post_thumbnail_id( $this->post_id ) );
			}

			$message = str_replace( '{' . $search . '}', $replace, $message );
		}

		return $message;
	}

	/**
	 * Validate the notification
	 *
	 * @return bool
	 */
	public function validate(): bool {
		return isset( $this->post_id );
	}
}
