<?php

/**
 * interface Provider_Interface
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 *
 */

namespace PushNotify\Provider;

use PushNotify\Message\Message;

interface ProviderInterface {
	public function send(Message $message);
    public function get_method();
}