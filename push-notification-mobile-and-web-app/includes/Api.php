<?php

namespace PushNotify;

defined( 'ABSPATH' ) || exit;

/**
 * Api Class
 */
class Api {

	/**
	 * All API Classes
	 *
	 * @var array
	 */
	protected $classes;

	/**
	 * App Builder Auth instance
	 *
	 * @var Api\AppBuilderKey
	 */
	protected $auth;

	/**
	 * Initialize
	 */
	public function __construct() {
		$this->classes = [
			Api\TokenApi::class,
			Api\SettingApi::class,
			Api\NotificationApi::class,
			Api\CustomNotificationApi::class,
		];

		// Initialize App Builder API Key authentication
		$this->auth = Api\AppBuilderKey::instance();
		$this->auth->init();

		add_action( 'rest_api_init', array( $this, 'init_api' ) );
	}

	/**
	 * Register APIs
	 *
	 * @return void
	 */
	public function init_api() {
		foreach ( $this->classes as $class ) {
			$object = new $class();
			$object->register_routes();
		}
	}
}