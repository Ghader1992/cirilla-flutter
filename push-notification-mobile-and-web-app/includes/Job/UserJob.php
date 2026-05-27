<?php

/**
 * class UserJob
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 */

namespace PushNotify\Job;

use PushNotify\Database\Token;
use PushNotify\Job\Job;

class UserJob extends Job {

	/**
	 * @var array
	 */
	public $id;

	/**
	 * UserJob constructor.
	 *
	 * @param $id
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * Handle job logic.
	 */
	public function handle() {
	}
}
