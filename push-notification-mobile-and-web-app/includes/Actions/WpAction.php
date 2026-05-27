<?php

/**
 * class WooAction
 *
 * @link       https://appcheap.io
 * @author     ngocdt
 * @since      1.2.0
 *
 */

namespace PushNotify\Actions;

use PushNotify\Notifications\Wp\Comment;
use PushNotify\Notifications\Wp\Post;
use PushNotify\Notifications\Wp\SavePost;
use WP_Post;

class WpAction {

	public function __construct() {
		add_action( 'comment_post', [ $this, 'comment_post' ] );

		$post_types = get_post_types( '' );

		$status = [ 'draft', 'future', 'pending', 'private', 'publish', 'trash' ];

		foreach ( $status as $type ) {
			foreach ( $post_types as $post_type ) {
				add_action( $type . '_' . $post_type, [ $this, 'post_type' ], 99, 3 );
			}
		}

		add_action( 'save_post', [ $this, 'save_post' ], 99, 3 );
	}

	/**
	 * Send message upon a new comment
	 *
	 * @link https://developer.wordpress.org/reference/hooks/comment_post/
	 *
	 * @param int $comment_id
	 *
	 * @return void
	 */
	public function comment_post( int $comment_id ) {
		$notifications = pushNotify()->settings()->notifications();
		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && $value['type'] == 'comment' && $value['status'] ) {
				$obj = new Comment();

				$obj->setId( $key );
				$obj->setComment( $comment_id );
				$obj->push();
			}
		}
	}

	/**
	 * Fires when a post is transitioned from one status to another.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/new_status_post-post_type/
	 *
	 * @param int $post_ID
	 * @param WP_Post $post
	 * @param string $old_status
	 */
	public function post_type( int $post_ID, WP_Post $post, string $old_status ) {
		$notifications = pushNotify()->settings()->notifications();
		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && $value['type'] == 'post' && $value['status'] ) {
				$obj = new Post();

				$obj->setId( $key )
				    ->setPost( $post_ID, $post, $old_status )
				    ->push();
			}
		}
	}

	/**
	 * Fires once a post has been saved.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/save_post/
	 *
	 * @param int $post_ID
	 * @param WP_Post $post
	 * @param bool $update
	 */
	public function save_post( int $post_ID, WP_Post $post, bool $update ) {
		// If this is a revision, don't send the email.
		if ( wp_is_post_revision( $post_ID ) ) {
			return;
		}

		// Gunter trigger 2 times
		if ( ( $post->post_type == 'post' || $post->post_type == 'page' ) && defined( 'REST_REQUEST' ) ) {
			return;
		}

		$notifications = pushNotify()->settings()->notifications();
		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && $value['type'] == 'save_post' && $value['status'] ) {
				$obj = new SavePost();
				$obj->setId( $key )
				    ->setPost( $post_ID, $post, $update )
				    ->push();
			}
		}
	}
}
