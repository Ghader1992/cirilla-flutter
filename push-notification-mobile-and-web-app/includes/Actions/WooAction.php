<?php

/**
 * class WooAction
 *
 * @link       https://appcheap.io
 * @author     ngocdt
 * @since      1.2.0
 *
 */

namespace PushNotify\Actions;

use PushNotify\Notifications\Wc\OrderStatusChanged;
use PushNotify\Notifications\Wc\ProductStatusChanged;

defined( 'ABSPATH' ) || exit;

class WooAction {

	public function __construct() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'woocommerce_order_status_changed' ], 10, 3 );
		add_action( 'transition_post_status', [ $this, 'transition_post_status' ], 10, 3 );
	}

	/**
	 * Fires when an order is transitioned from one status to another.
	 *
	 * @param $id
	 * @param $previous_status
	 * @param $next_status
	 */
	public function woocommerce_order_status_changed( $id, $previous_status, $next_status ) {

		$notifications = pushNotify()->settings()->notifications();

		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && $value['type'] == 'order_status_changed' && $value['status'] ) {
				$obj = new OrderStatusChanged();

				$obj->setId( $key )
				    ->setOrderId( $id )
				    ->setNextStatus( $next_status )
				    ->setPreviousStatus( $previous_status )
				    ->push();
			}
		}
	}

	/**
	 * Fires when a product is transitioned from one status to another.
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		$notifications = pushNotify()->settings()->notifications();

		if ( $post->post_type != "product" ) {
			return;
		}

		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && $value['type'] == 'product_status_changed' && $value['status'] ) {
				$obj = new ProductStatusChanged();

				$obj->setId( $key )
				    ->setPost( $new_status, $old_status, $post )
				    ->push();
			}
		}
	}
}
