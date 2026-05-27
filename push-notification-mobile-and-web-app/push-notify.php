<?php
/**
 *
 * @link              https://appcheap.io
 * @since             1.0.0
 * @package           PushNotify
 * @category          includes
 *
 * @wordpress-plugin
 * Plugin Name:       Push notification for Mobile and Web app
 * Plugin URI:        https://appcheap.io/push-notification-mobile-and-web-app
 * Description:       Handle trigger action and manual push notification for Mobile, Web and In-app messages
 * Version:           2.0.4
 * Author:            Appcheap.io
 * Author URI:        https://appcheap.io
 * Text Domain:       push-notify
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PUSH_NOTIFY_PLUGIN_FILE' ) ) {
	define( 'PUSH_NOTIFY_PLUGIN_FILE', __FILE__ );
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main PushNotify Class.
 *
 * @class PushNotify
 *
 * @since 1.0.0
 */
final class PushNotify {
	/**
	 * PushNotify js version.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $js_version = '1.8.1';

	/**
	 * PushNotify version.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $version = '1.8.1';

	/**
	 * The plugin url.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $plugin_url;

	/**
	 * The plugin path.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $plugin_path;

	/**
	 * AppCheap Schema version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public string $db_version = '1.2.0';

	/**
	 * The single instance of the class.
	 *
	 * @var ?PushNotify
	 * @since 1.0.0
	 */
	protected static ?PushNotify $_instance = null;

	/**
	 * Instances of services
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private array $services = array();

	/**
	 * Main PushNotify Instance.
	 *
	 * Ensures only one instance of push notify is loaded.
	 *
	 * @return PushNotify - Main instance.
	 * @since 1.0.0
	 * @static
	 */
	public static function instance(): ?PushNotify {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * PushNotify Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->define_constants();
		$this->init_hooks();
	}

	/**
	 * Define PushNotify Constants.
	 *
	 * @since 1.0.0
	 */
	private function define_constants() {
		$this->define( 'PUSH_NOTIFY_NAME', 'push_notify' );
		$this->define( 'PUSH_NOTIFY_DOMAIN', 'push-notify' );
		$this->define( 'PUSH_NOTIFY_ABSPATH', dirname( PUSH_NOTIFY_PLUGIN_FILE ) . DIRECTORY_SEPARATOR );
		$this->define( 'PUSH_NOTIFY_PLUGIN_BASENAME', plugin_basename( PUSH_NOTIFY_PLUGIN_FILE ) );
		$this->define( 'PUSH_NOTIFY_ASSETS', plugin_dir_url( PUSH_NOTIFY_PLUGIN_FILE ) . 'assets' );
		$this->define( 'PUSH_NOTIFY_TEMPLATES', plugin_dir_path( PUSH_NOTIFY_PLUGIN_FILE ) . 'templates' );
		$this->define( 'PUSH_NOTIFY_VERSION', $this->version );
		$this->define( 'PUSH_NOTIFY_JS_VERSION', $this->js_version );
		$this->define( 'PUSH_NOTIFY_DB_VERSION', $this->db_version );
		$this->define( 'PUSH_NOTIFY_TOKENS', 'push_notify_tokens' );
		$this->define( 'PUSH_NOTIFY_REST_API_NOTIFICATION', 'https://fcm.googleapis.com/fcm/send' );
		$this->define( 'PUSH_NOTIFY_CDN_JS', 'https://appcheap-push-notify.web.app/' . $this->js_version );
		$this->define( 'PUSH_NOTIFY_CAPABILITY', 'manage_options' );
		$this->define( 'PUSH_NOTIFY_QUEUE_JOBS', false );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name Constant name.
	 * @param string|bool $value Constant value.
	 *
	 * @since 1.0.0
	 */
	private function define( string $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Get constant
	 *
	 * @param string $name
	 *
	 * @return mixed|WP_Error
	 */
	public function constant( string $name ) {
		if ( ! defined( $name ) ) {
			return constant( $name );
		}

		return new WP_Error( 'app_builder_constant', __( 'The constant did not defined!', 'push-notify' ) );
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		register_activation_hook( PUSH_NOTIFY_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( PUSH_NOTIFY_PLUGIN_FILE, array( $this, 'deactivate' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * The plugin loaded
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function plugins_loaded() {

		if ( is_admin() ) {
			new PushNotify\Admin();
		} else {
			new PushNotify\Frontend();
		}
		new PushNotify\Api();
		new PushNotify\I18n();
		new PushNotify\PostTypes();
	}

	/**
	 * The plugin init
	 *
	 * @return void
	 */
	public function init() {
		new PushNotify\Action();
	}

	/**
	 *
	 * Returns service Setting
	 *
	 * @return PushNotify\Settings|mixed
	 */
	public function settings() {
		if ( ! isset( $this->services['settings'] ) ) {
			$this->services['settings'] = new PushNotify\Settings();
		}

		return $this->services['settings'];
	}

	/**
	 *
	 * Returns service Notification
	 *
	 * @return PushNotify\Provider\ProviderInterface
	 */
	public function notification() {

		$method = $this->settings()->get( 'method' );

		if ( ! isset( $this->services[ 'notification' . $method ] ) ) {
			if ( 'firebase' === $method ) {
				$this->services[ 'notification' . $method ] = new PushNotify\Provider\FirebaseProvider();
			} if ( 'firebasev1' === $method ) {
				$this->services[ 'notification' . $method ] = new PushNotify\Provider\FirebaseProviderV1();
			} elseif ( 'signal' === $method ) {
				$this->services[ 'notification' . $method ] = new PushNotify\Provider\SignalProvider();
			} else {
				$this->services[ 'notification' . $method ] = new PushNotify\Provider\DebugProvider();
			}
		}

		return $this->services[ 'notification' . $method ];
	}

	/**
	 *
	 * Returns service Send
	 *
	 * @return PushNotify\Send\Send|mixed
	 */
	public function send() {
		$method = $this->settings()->get( 'method' );

		if ( ! isset( $this->services[ 'send' . $method ] ) ) {
			if ( 'firebase' === $method ) {
				$this->services[ 'send' . $method ] = PushNotify\Send\Send::class;
			} elseif ( 'signal' === $method ) {
				$this->services[ 'send' . $method ] = PushNotify\Send\SendOneSignal::class;
			} else {
				$this->services[ 'send' . $method ] = PushNotify\Send\Send::class;
			}
		}

		return $this->services[ 'send' . $method ];
	}

	/**
	 * Handle push job
	 *
	 * @param Job $job
	 */
	public function push( $job ) {
		if ( constant( 'PUSH_NOTIFY_QUEUE_JOBS' ) ) {
			// wp_queue()->push( $job );
            $job->handle();
		} else {
			$job->handle();
		}
	}

	/**
	 * The plugin activation function.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function activate() {
		$activator = new PushNotify\Activator();
		$activator->activate();
	}

	/**
	 * The plugin deactivation function.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function deactivate() {
		$deactivated = new PushNotify\Deactivate();
		$deactivated->deactivate();
	}
}

/**
 * Returns the main instance of PushNotify.
 *
 * @return PushNotify
 * @since  1.0.0
 */
function pushNotify(): PushNotify {
	return PushNotify::instance();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
$GLOBALS['push_notify'] = pushNotify();
