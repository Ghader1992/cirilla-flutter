<?php

/**
 * Admin
 *
 * @link       https://appcheap.io
 * @since      1.0.0
 * @author     ngocdt
 *
 */

namespace PushNotify;

defined( 'ABSPATH' ) || exit;

/**
 * Post types Class.
 */

if ( ! class_exists( 'PostTypes' ) ) {
	class PostTypes {

		/**
		 * Post_Types constructor.
		 *
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register_taxonomies' ), 5 );
			add_action( 'init', array( $this, 'register_post_types' ), 5 );
		}

		/**
		 * Register core taxonomies.
		 */
		public function register_taxonomies() {
		}

		/**
		 * Register core post types.
		 */
		public function register_post_types() {
		}
	}
}
