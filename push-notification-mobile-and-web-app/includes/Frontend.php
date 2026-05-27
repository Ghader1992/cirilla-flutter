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

class Frontend {
	public function __construct() {
		/**
		 * Add style for checkout page
		 * @since 1.0.0
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {}
}
