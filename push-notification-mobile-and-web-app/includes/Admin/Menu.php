<?php

namespace PushNotify\Admin;

use PushNotify\Base;
use PushNotify\Notifications;

/**
 * @package    PushNotify
 * @author     Appcheap <ngocdt@rnlab.io>
 */
class Menu extends Base {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	public function __construct() {

		$this->plugin_name = constant( 'PUSH_NOTIFY_DOMAIN' );
		$this->version     = constant( 'PUSH_NOTIFY_JS_VERSION' );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ), 9 );

		// Add plugin action link point to settings page
		add_filter( 'plugin_action_links_' . constant( 'PUSH_NOTIFY_PLUGIN_BASENAME' ), array(
			$this,
			'add_plugin_action_links'
		) );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		/*
		 * Add a settings page for this plugin to the Settings menu.
		 */
		$hook_suffix = add_menu_page(
			__( 'Push Notifications', constant( 'PUSH_NOTIFY_DOMAIN' ) ),
			__( 'Push Notifications', constant( 'PUSH_NOTIFY_DOMAIN' ) ),
			constant( 'PUSH_NOTIFY_CAPABILITY' ),
			'push-notify',
			array( $this, 'display_plugin_admin_page' ),
			constant( 'PUSH_NOTIFY_ASSETS' ) . '/icon/bell.svg'
		);

		// Load enqueue styles and script
		add_action( "admin_print_styles-$hook_suffix", array( $this, 'enqueue_styles' ) );
		add_action( "admin_print_scripts-$hook_suffix", array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			constant( 'PUSH_NOTIFY_CDN_JS' ) . '/static/css/main.css',
			array()
			, $this->version
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_media();

		wp_enqueue_script(
			$this->plugin_name,
			constant( 'PUSH_NOTIFY_CDN_JS' ) . '/static/js/main.js',
			array(
				'jquery',
				'media-upload',
			),
			$this->version,
			true
		);

		$notify = new Notifications();
		$roles  = [];

		foreach ( wp_roles()->roles as $key => $value ) {
			$roles[ $key ] = [ 'name' => $value['name'] ];
		}

		$data = 'window.' . constant( 'PUSH_NOTIFY_NAME' ) . '=' . json_encode( [
				'api_nonce' => wp_create_nonce( 'wp_rest' ),
				'api_url'   => rest_url(),
				'version'   => constant( 'PUSH_NOTIFY_VERSION' ),
				'settings'  => pushNotify()->settings()->configs(),
				'data'      => array(
					'roles'               => $roles,
					'groups'              => $notify->get_groups(),
					'actions'             => $notify->get_actions(),
					'custom_notification' => $notify->getCustomNotificationInfo(),
					'actions_sample'      => $notify->getActionSample(),
				)
			] );

		wp_add_inline_script( constant( 'PUSH_NOTIFY_DOMAIN' ), $data, 'before' );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		?>
        <div id="push-notify"></div>
		<?php
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @param $links
	 *
	 * @return array
	 * @since    1.0.0
	 */
	public function add_plugin_action_links( $links ): array {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'edit.php?post_type=push_notification&page=push-notify' ) . '">' . __( 'Settings', 'push-notify' ) . '</a>',
			),
			$links
		);
	}
}
