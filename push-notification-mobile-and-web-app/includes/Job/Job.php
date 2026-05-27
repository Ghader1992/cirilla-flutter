<?php
/**
 * The Job class
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 * @package    PushNotify\Job
 */

namespace PushNotify\Job;

/**
 * Class Job
 *
 * @package PushNotify\Job
 */
abstract class Job {

	/**
	 * Handle job logic
	 */
	abstract public function handle();
}
