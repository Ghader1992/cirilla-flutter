<?php

/**
 * class Message
 *
 * @link       https://appcheap.io
 * @since      1.2.0
 *
 * @author     AppCheap <ngocdt@rnlab.io>
 */

namespace PushNotify\Message;

/**
 * Message Object
 */
class Message {

	/**
	 * @var String Registration token to send a message to.
	 */
	public string $token;

	/**
	 * @var String Topic name to send a message to, e.g. "weather". Note: "/topics/" prefix should not be provided.
	 */
	public string $topic;

	/**
	 * @var array Registration token's to send a message to.
	 */
	public array $registration_ids;

	/**
	 * @var String Condition to send a message to, e.g. "'foo' in topics && 'bar' in topics".
	 */
	public string $condition;

	/**
	 * @var String The notification's title.
	 */
	public string $title = '';

	/**
	 * @var String The notification's body text.
	 */
	public string $body = '';

	/**
	 * @var String The notification's sound.
	 */
	public string $sound = '';

	/**
	 * Contains the URL of an image that is going to be downloaded on the device and displayed in a notification.
	 * JPEG, PNG, BMP have full support across platforms.
	 * Animated GIF and video only work on iOS. WebP and HEIF have varying levels of support across platforms and platform versions.
	 * Android has 1MB image size limit.
	 *
	 * @var ?String The Image URL
	 */
	public ?string $image = null;

	/**
	 * The filters parameter targets notification recipients using an array of JSON objects containing field conditions to check. The following are filter field options:
	 *
	 * @var array Filters
	 * @see https://documentation.onesignal.com/reference/create-notification#formatting-filters
	 */
	public array $filters = array();

	/**
	 *
	 * Arbitrary key/value payload, which must be UTF-8 encoded.
	 * The key should not be a reserved word ("from", "message_type", or any word starting with "google" or "gcm").
	 * When sending payloads containing only data fields to iOS devices, only normal priority ("apns-priority": "5") is allowed in ApnsConfig.
	 *
	 * An object containing a list of "key": value pairs. Example:
	 *
	 * ```json
	 *    { "name": "wrench", "mass": "1.3kg", "count": "3" }.
	 * ```
	 *
	 * @var array map (key: string, value: string)
	 */
	public array $data;

	/**
	 * @var array|string[][][] iOS priority
	 */
	public array $iOS = array(
		'apns' => array(
			'headers' => array(
				'apns-priority' => '5',
			),
		),
	);

	/**
	 * @var array|string[][] Android priority
	 */
	public array $android = array(
		'android' => array(
			'priority' => 'normal',
		),
	);

	/**
	 * @var array|string[][][] Web priority
	 */
	public array $web = array(
		'webpush' => array(
			'headers' => array(
				'Urgency' => 'high',
			),
		),
	);

	public function __construct( string $title, string $body ) {
		$this->title = $title;
		$this->body  = $body;
	}

	/**
	 * Get payload notification
	 *
	 * @return array
	 */
	public function getNotification(): array {
		return array(
			'title' => $this->translate( $this->title ),
			'body'  => wp_strip_all_tags( $this->translate( $this->body ) ),
			'image' => $this->translate( $this->image ),
			'sound' => $this->sound,
		);
	}

	/**
	 * Get payload notification FCM V1
	 *
	 * @return array
	 */
	public function getNotificationV1(): array {
		return array(
			'title' => $this->translate( $this->title ),
			'body'  => wp_strip_all_tags( $this->translate( $this->body ) ),
			'image' => $this->translate( $this->image ),
		);
	}

	/**
	 * Translate the text
	 *
	 * @param $text
	 *
	 * @return string
	 */
	protected function translate( $text ): string {

		if ( empty( $text ) ) {
			return '';
		}

		$translations = pushNotify()->settings()->translations();

		if ( empty( $translations ) || ! is_array( $translations ) || count( $translations ) == 0 ) {
			return $text;
		}

		$message = $text;

		foreach ( $translations as $translation ) {
			if ( isset( $translation['string'] ) && isset( $translation['translate'] ) ) {
				$message = str_replace( $translation['string'], $translation['translate'], $message );
			}
		}

		return $message;
	}

	/**
	 * Get payload data
	 *
	 * @return array
	 */
	public function getData(): array {
		if ( ! empty( $this->data ) && is_array( $this->data ) ) {
			$results = array();
			foreach ( $this->data as $key => $value ) {
				if ( is_array( $value ) ) {
					$results[ $key ] = json_encode( $value );
				} else {
					$results[ $key ] = $value;
				}
			}

			return $results;
		}

		return array();
	}

	/**
	 * @param array $data
	 *
	 * @return $this
	 */
	public function setData( array $data ): Message {
		$this->data = $data;

		return $this;
	}

	/**
	 * Get priority of a message
	 *
	 * @param String $platform
	 *
	 * @return string[][]|string[][][]
	 */
	public function getPriority( string $platform = 'all' ): array {
		if ( $platform == 'ios' ) {
			return $this->iOS;
		}
		if ( $platform == 'android' ) {
			return $this->android;
		}
		if ( $platform == 'web' ) {
			return $this->web;
		}

		return array_merge( $this->web, $this->iOS, $this->android );
	}

	/**
	 * @return String
	 */
	public function getToken(): string {
		return $this->token;
	}

	/**
	 * @param String $token
	 */
	public function setToken( string $token ): void {
		$this->token = $token;
	}

	/**
	 * @return String
	 */
	public function getTopic(): string {
		return $this->topic;
	}

	/**
	 * @param String $topic
	 */
	public function setTopic( string $topic ): void {
		$this->topic = $topic;
	}

	/**
	 * @return array
	 */
	public function getRegistrationIds(): array {
		return $this->registration_ids;
	}

	/**
	 * @param array $registration_ids
	 */
	public function setRegistrationIds( array $registration_ids ): void {
		$this->registration_ids = $registration_ids;
	}

	/**
	 * @return String
	 */
	public function getCondition(): string {
		return $this->condition;
	}

	/**
	 * @param String $condition
	 */
	public function setCondition( string $condition ): void {
		$this->condition = $condition;
	}

	/**
	 * @return String
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * @param String $title
	 */
	public function setTitle( string $title ): void {
		$this->title = $title;
	}

	/**
	 * @return String
	 */
	public function getBody(): string {
		return $this->body;
	}

	/**
	 * @param String $body
	 */
	public function setBody( string $body ): void {
		$this->body = $body;
	}

	/**
	 * @return String
	 */
	public function getImage(): string {
		return $this->image;
	}

	/**
	 * @param String $image
	 */
	public function setImage( string $image ): void {
		$this->image = $image;
	}

	/**
	 * @return String
	 */
	public function getSound(): string {
		return $this->sound;
	}

	/**
	 * @param String $sound
	 */
	public function setSound( string $sound ): void {
		$this->iOS['apns'] = array_merge($this->iOS['apns'], array(
			'payload' => array(
				'aps' => array(
					'sound' => $sound,
				),
			),
		));
		$this->sound = $sound;
	}

	/**
	 * @return array|string[][][]
	 */
	public function getIOS(): array {
		return $this->iOS;
	}

	/**
	 * @param array|string[][][] $iOS
	 */
	public function setIOS( array $iOS ): void {
		$this->iOS = $iOS;
	}

	/**
	 * @return array|string[][]
	 */
	public function getAndroid(): array {
		return $this->android;
	}

	/**
	 * @param array|string[][] $android
	 */
	public function setAndroid( array $android ): void {
		$this->android = $android;
	}

	/**
	 * @return array|string[][][]
	 */
	public function getWeb(): array {
		return $this->web;
	}

	/**
	 * @param array|string[][][] $web
	 */
	public function setWeb( array $web ): void {
		$this->web = $web;
	}

	/**
	 * Set filters
	 *
	 * @param array $filters Filters.
	 */
	public function set_filters( array $filters ): void {
		$this->filters = $filters;
	}

	/**
	 * Get filters
	 */
	public function get_filters(): array {
		return $this->filters;
	}


	/**
	 *
	 * Convert to payload data
	 *
	 * @return array[]
	 */
	public function toMessage(): array {

		$data = array(
			'notification' => $this->getNotification(),
		);

		if ( count( $this->getData() ) > 0 ) {
			$data['data'] = $this->getData();
		}

		if ( ! empty( $this->token ) ) {
			$data['token'] = $this->token;
		}

		if ( ! empty( $this->registration_ids ) ) {
			$data['registration_ids'] = $this->registration_ids;
		}

		if ( ! empty( $this->topic ) ) {
			$data['to'] = '/topics/' . $this->topic;
		}

		if ( ! empty( $this->condition ) ) {
			$data['condition'] = $this->condition;
		}

		return array_merge( $data, $this->getPriority() );
	}

	/**
	 * To Firebase Cloud Messaging V1 API
	 */
	public function toFcmV1(): array {
		$data = array(
			'notification' => $this->getNotificationV1(),
		);

		if ( count( $this->getData() ) > 0 ) {
			$data['data'] = $this->getData();
		}

		if ( ! empty( $this->token ) ) {
			$data['token'] = $this->token;
		}

		if ( ! empty( $this->topic ) ) {
			$data['topic'] = $this->topic;
		}

		if ( ! empty( $this->condition ) ) {
			$data['condition'] = $this->condition;
		}

		return array_merge( $data, $this->getPriority() );
	}

	/**
	 * Convert to payload data
	 *
	 * @param string $onesignal_app_id OneSignal App ID.
	 *
	 * @return array
	 */
	public function to_one_signal_body_message( string $onesignal_app_id ): array {

		$sound = 'nil';

		if ( ! empty( $this->sound ) ) {
			$sound = $this->sound;
		}

		$data = array(
			'app_id'             => $onesignal_app_id,
			'contents'           => array(
				'en' => wp_strip_all_tags( $this->body ),
			),
			'headings'           => array(
				'en' => $this->title,
			),
			'big_picture'        => $this->image,
			'ios_attachments'    => array(
				'id1' => $this->image,
			),
			'small_icon'         => 'ic_notification',
			'huaewi_small_icon'  => 'ic_notification',
			'huawei_big_picture' => $this->image,
			'android_sound'      => $sound,
			'ios_sound'          => $sound,
			'huawei_sound'       => $sound,
		);

		if ( count( $this->getData() ) > 0 ) {
			$data['data'] = $this->getData();
		}

		if ( empty( $this->filters ) && ! empty( $this->registration_ids ) ) {
			$data['include_subscription_ids'] = $this->registration_ids;
		}

		if ( empty( $this->registration_ids ) && ! empty( $this->filters ) ) {
			$data['filters'] = $this->filters;
		}

		return $data;
	}
}
