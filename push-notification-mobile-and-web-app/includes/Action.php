<?php
namespace PushNotify;

use PushNotify\Actions\WcfmAction;
use PushNotify\Actions\WooAction;
use PushNotify\Actions\WpAction;
use PushNotify\Actions\BbAction;

defined( 'ABSPATH' ) || exit;

/**
 * Class Action
 *
 * @package PushNotify
 */
class Action {
	/**
	 * Action constructor.
	 */
	public function __construct() {
		new WpAction();
		new WooAction();
		new WcfmAction();
		new BbAction();
	}
}
