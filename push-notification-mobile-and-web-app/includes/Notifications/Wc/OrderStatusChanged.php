<?php

namespace PushNotify\Notifications\Wc;

use PushNotify\Notifications\Notification;

class OrderStatusChanged extends Notification {

	/**
	 * @var int comment id
	 */
	private int $order_id;
	private string $previous_status;
	private string $next_status;

	/**
	 * @param int $order_id
	 *
	 * @return OrderStatusChanged
	 */
	public function setOrderId( int $order_id ): OrderStatusChanged {
		$this->order_id = $order_id;

		return $this;
	}

	/**
	 * @param string $previous_status
	 *
	 * @return OrderStatusChanged
	 */
	public function setPreviousStatus( string $previous_status ): OrderStatusChanged {
		$this->previous_status = $previous_status;

		return $this;
	}

	/**
	 * @param string $next_status
	 *
	 * @return OrderStatusChanged
	 */
	public function setNextStatus( string $next_status ): OrderStatusChanged {
		$this->next_status = $next_status;

		return $this;
	}

	public function __construct() {
		$this->name  = $this->trans( 'Order status changed' );
		$this->group = 'wc';
		$this->type  = 'order_status_changed';

		$this->default_title      = $this->trans( 'New order received' );
		$this->default_body       = $this->trans( 'New order received #{order_id}' );
		$this->default_recipients = [
			[ 'key' => '123456', 'type' => 'registration_ids', 'value' => [ 'ios' ] ],
		];

		$this->default_info = array_merge(
			$this->getDefaultBaseInfo(), [],
		);
	}

	/**
	 * Get merge tags keys
	 *
	 * @return array
	 */
	public function mergeTags(): array {
		return [
			'order_id'        => 'get_id',
			'customer_id'     => 'get_customer_id',
			'items'           => 'products',
			'date'            => 'get_date_paid',
			'status'          => 'get_status',
			'payment_method'  => 'get_payment_method_title',
			'shipping_method' => 'get_shipping_method',
			'transaction_id'  => 'get_transaction_id',
			'billing_name'    => 'get_formatted_billing_full_name',
			'billing_email'   => 'get_billing_email',
			'order_total'     => 'get_formatted_order_total',
			'shipping_total'  => 'get_shipping_total',
			'tax_total'       => 'get_total_tax',
			'discount'        => 'get_discount_total',
			'previous_status' => 'previous_status',
			'next_status'     => 'next_status',
		];
	}

	/**
	 * Return the data after replace
	 *
	 * @param string|null $str
	 *
	 * @return string
	 */
	public function replaceTags( ?string $str ): string {
		if ( empty( $str ) ) {
			return '';
		}

		if ( ! is_string( $str ) ) {
			return $str;
		}

		$obj     = wc_get_order( $this->order_id );
		$message = $str;

		foreach ( $this->mergeTags() as $search => $value ) {

			$replace = method_exists( $obj, $value ) ? call_user_func( [ $obj, $value ] ) : '';

			if ( $search == 'previous_status' ) {
				$replace = $this->previous_status;
			}

			if ( $search == 'next_status' ) {
				$replace = $this->next_status;
			}

			$message = str_replace( '{' . $search . '}', $replace, $message );
		}

		return $message;
	}

	public function validate(): bool {
		return isset( $this->order_id );
	}

	/**
	 * Prepare merge tag
	 *
	 * @param $recipients
	 *
	 * @return array
	 */
	public function prepare_recipients( $recipients ): array {

		$obj  = wc_get_order( $this->order_id );
		$data = [];

		foreach ( $recipients as $value ) {
			$recipient = $value;
			if ( $recipient['type'] == 'email_tag' && ! is_email( $recipient['value'] ) ) {
				switch ( $recipient['value'] ) {
					case '{billing_email}':
					case '{get_billing_email}':
						$recipient['value'] = $obj->get_billing_email();
						break;
					case '{customer_id}':
						$customer_id = $obj->get_customer_id();
						if ( is_int( $customer_id ) && $customer_id > 0 ) {
							$recipient = [
								'type'  => 'users',
								'value' => [ [ 'key' => $customer_id ] ]
							];
						}
						break;
					default:
						break;
				}
			}

			$data[] = $recipient;
		}

		return $data;
	}
}

