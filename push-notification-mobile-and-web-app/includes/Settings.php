<?php

namespace PushNotify;

/**
 * Class Setting
 * @author ngocdt@rnlab.io
 * @since 1.0.0
 */
class Settings {

	/**
	 * Option key store in database
	 */
	public string $option_key = 'push_notify_settings_v3';

	/**
	 *
	 * Default settings
	 *
	 * @var array
	 */
	protected array $default_settings = [
		'method'              => 'debug',
		'firebase_server_key' => '',
		'notifications'       => [],
		'string_translations' => [],
	];

	/**
	 * Get all the settings
	 *
	 * @return array
	 */
	public function configs(): array {
		// Default settings
		$default = apply_filters( 'push_notify_settings_default', $this->default_settings );

		// Get configs
		$settings = get_option( $this->option_key, [] );

		return apply_filters( 'push_notify_settings', wp_parse_args( $settings, $default ) );
	}

	/**
	 *
	 * Get setting by key
	 *
	 * @param string $key
	 *
	 * @return false|mixed
	 */
	public function get( string $key ) {
		$settings = $this->configs();

		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}

		return false;
	}

	/**
	 * Get notifications data
	 *
	 * @return mixed
	 */
	public function notifications() {
		$settings = $this->configs();

		if ( isset( $settings['notifications'] ) ) {
			return $settings['notifications'];
		}

		return $this->default_settings['notifications'];
	}

	/**
	 * Get string translations data
	 *
	 * @return mixed
	 */
	public function translations() {
		$settings = $this->configs();

		if ( isset( $settings['string_translations'] ) ) {
			return $settings['string_translations'];
		}

		return $this->default_settings['string_translations'];
	}

	/**
	 *
	 * Update or create settings
	 *
	 * @param array $value
	 *
	 * @return bool
	 */
	public function set( array $value ): bool {

		// Default settings
		$default = apply_filters( 'push_notify_settings_default', $this->default_settings );

		$settings = wp_parse_args( $value, $default );

		return update_option( $this->option_key, $settings, false );
	}
}
