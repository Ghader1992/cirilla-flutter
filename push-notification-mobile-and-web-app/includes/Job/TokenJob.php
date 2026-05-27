<?php

/**
 * class TokenJob
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 */

namespace PushNotify\Job;

use PushNotify\Database\Token;
use PushNotify\Job\Job;

class TokenJob extends Job {

	/**
	 * @var array
	 */
	public array $bodyContent;
	public array $registration_ids;

	/**
	 * TokenJob constructor.
	 *
	 * @param $bodyContent
	 * @param $registration_ids
	 */
	public function __construct( $bodyContent, $registration_ids ) {
		$this->bodyContent      = $bodyContent;
		$this->registration_ids = $registration_ids;
	}

	/**
	 * Handle job logic.
	 */
	public function handle() {

		$tokenExpired = array();

		$bodyContent      = $this->bodyContent;
		$registration_ids = $this->registration_ids;

		if ( isset( $bodyContent['failure'] ) && $bodyContent['failure'] ) {
			for ( $i = 0; $i < count( $bodyContent['results'] ); $i++ ) {
				if ( array_key_exists( 'error', $bodyContent['results'][ $i ] ) ) {
					$tokenExpired[] = $registration_ids[ $i ];
				}
			}
		}

		// Handle remove tokens expired
		$tokenDb = new Token();
		$tokenDb->remove_tokens_expired( $tokenExpired );
	}
}
