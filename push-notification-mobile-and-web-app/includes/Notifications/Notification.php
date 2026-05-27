<?php

namespace PushNotify\Notifications;

use PushNotify\Base;
use PushNotify\Functions;
use PushNotify\Job\NotificationJob;
use PushNotify\Message\Message;

abstract class Notification extends Base {
	/**
	 * @var string notification id
	 */
	protected string $id;

	/**
	 * @var string notification group
	 */
	protected string $group;

	/**
	 * @var string notification name
	 */
	protected string $name;

	/**
	 * @var string notification type
	 */
	protected string $type;

	protected string $default_title     = '';
	protected string $default_body      = '';
	protected array $default_info       = array();
	protected array $default_recipients = array();

	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType( string $type ): void {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param string $id
	 *
	 * @return Notification
	 */
	public function setId( string $id ): Notification {
		$this->id = $id;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getDefaultInfo(): array {
		return $this->default_info;
	}

	/**
	 * @return array
	 */
	public function getDefaultBaseInfo(): array {
		return array(
			'name'         => array(
				'label'       => $this->trans( 'Name' ),
				'placeholder' => $this->trans( 'Enter notification name' ),
				'hint'        => $this->trans( 'Name used to identify this notification on Dashboard. This name is not shown to users.' ),
				'description' => '',
			),
			'title'        => array(
				'label'       => $this->trans( 'Notification title' ),
				'placeholder' => $this->trans( 'Enter optional title' ),
				'hint'        => $this->trans( 'Shown to end users as the notification title' ),
				'description' => '',
			),
			'body'         => array(
				'label'       => $this->trans( 'Notification text' ),
				'placeholder' => $this->trans( 'Enter notification text' ),
				'hint'        => '',
				'description' => '',
			),
			'image'        => array(
				'label'       => $this->trans( 'Notification image (optional) ' ),
				'placeholder' => '',
				'hint'        => $this->trans( 'Optionally upload an image or provide a valid HTTPS image URL' ),
				'description' => 'Keep in mind that a 300KB max image size is enforced by the device.',
			),
			'sound'        => array(
				'label'       => $this->trans( 'Notification sound (optional) ' ),
				'placeholder' => '',
				'hint'        => $this->trans( 'The sound to play when the device receives the notification.' ),
				'description' => 'String specifying sound files in the main bundle of the client app or in the Library/Sounds folder of the app\'s data container. See the iOS Developer Library for more information.',
			),
			'recipients'   => array(
				'label'       => $this->trans( 'Recipients' ),
				'placeholder' => '',
				'hint'        => '',
				'description' => '',
			),
			'action'       => array(
				'label'       => $this->trans( 'Action (optional)' ),
				'placeholder' => '',
				'hint'        => '',
				'description' => '',
			),
			'conditionals' => array(
				'label'       => $this->trans( 'Conditionals' ),
				'placeholder' => '',
				'hint'        => '',
				'description' => '',
			),
		);
	}

	/**
	 * @return string
	 */
	public function getGroup(): string {
		return $this->group;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName( string $name ): void {
		$this->name = $name;
	}

	/**
	 * Get notification settings
	 *
	 * @return array|mixed
	 */
	public function getSettings() {
		$settings = pushNotify()->settings()->notifications();

		if ( isset( $this->id ) && isset( $settings[ $this->id ] ) ) {
			return $settings[ $this->id ];
		}

		return array();
	}

	/**
	 * Get message title
	 *
	 * @return mixed|string
	 */
	public function getTitle() {
		$setting = $this->getSettings();

		return $setting['title'] ?? $this->default_title;
	}

	/**
	 * Get message body
	 *
	 * @return mixed|string
	 */
	public function getBody() {
		$setting = $this->getSettings();

		return $setting['body'] ?? $this->default_body;
	}

	/**
	 * Get message title
	 *
	 * @return mixed|string
	 */
	public function getImage() {
		$setting = $this->getSettings();

		return $setting['image'] ?? '';
	}

	/**
	 * Get message sound
	 *
	 * @return mixed|string
	 */
	public function getSound() {
		$setting = $this->getSettings();

		return $setting['sound'] ?? '';
	}

	/**
	 * Get message recipients
	 *
	 * @return mixed
	 */
	public function getRecipients() {
		$setting = $this->getSettings();

		$recipients = $setting['recipients'] ?? $this->default_recipients;
		// Use merge tags if recipient is string.
		foreach ( $recipients as $key => $recipient ) {
			if ( is_string( $recipient['value'] ) ) {
				$recipients[ $key ] = array(
					...$recipient,
					'value' => $this->replaceTags( $recipient['value'] ),
					'tag'   => $recipient['value'],
				);
			}
		}
		return $recipients;
	}

	/**
	 * Get message status
	 *
	 * @return mixed
	 */
	public function getStatus(): bool {
		$setting = $this->getSettings();

		return (bool) $setting['status'];
	}

	/**
	 * Replace tags
	 *
	 * @param string|null $str
	 *
	 * @return string
	 */
	public function replaceTags( ?string $str ): string {
		return $str ?? '';
	}

	/**
	 * Validate data before create message
	 *
	 * @return bool
	 */
	public function validate(): bool {
		return true;
	}

	/**
	 * Get message
	 *
	 * @return Message|null
	 */
	public function getMessage(): ?Message {
		if ( ! $this->validate() ) {
			return null;
		}

		$title = $this->getTitle();
		$body  = $this->getBody();

		$message = new Message(
			$this->replaceTags( $title ),
			$this->replaceTags( $body ),
		);

		if ( ! empty( $this->getImage() ) ) {
			$image = $this->replaceTags( $this->getImage() );
			$message->setImage( $image );
		}

		if ( ! empty( $this->getSound() ) ) {
			$sound = $this->replaceTags( $this->getSound() );
			$message->setSound( $sound );
		}

		return $message;
	}

	/**
	 * Set notification deeplink
	 *
	 * Noted: The data in array contains
	 *
	 * action => type
	 * action => router
	 * action => action
	 *
	 * @return string[]
	 */
	public function navigate(): array {

		$setting = $this->getSettings();

		if ( isset( $setting['action'] ) && is_array( $setting['action'] ) ) {

			$action = $setting['action'];

			if ( isset( $action['type'] ) ) {
				$action['type'] = $this->replaceTags( $action['type'] );
			}

			if ( isset( $action['router'] ) ) {
				$action['route'] = $this->replaceTags( $action['router'] );
				unset( $action['router'] );
			}

			if ( isset( $action['action'] ) && is_array( $action['action'] ) && count( $action['action'] ) > 0 ) {
				$args = array();

				foreach ( $action['action'] as $key => $value ) {
					$args[ $key ] = $this->replaceTags( $value );
				}
				$action['args'] = $args;
				unset( $action['action'] );
			}

			return $action;
		}

		return array(
			'route' => 'none',
		);
	}

	/**
	 * Get data when conditionals
	 *
	 * @return string
	 */
	public function getDataWhenConditionals(): string {
		$setting = $this->getSettings();

		if ( isset( $setting['when_conditionals'] ) ) {
			return $setting['when_conditionals'];
		}

		return 'always';
	}

	/**
	 * Get conditionals data
	 *
	 * @return array
	 */
	public function getDataConditionals(): array {
		$setting = $this->getSettings();
		if ( isset( $setting['conditionals'] ) && is_array( $setting['conditionals'] ) ) {
			return $setting['conditionals'];
		}

		return array();
	}

	public function whenConditionals(): array {
		return array(
			'always'         => $this->trans( 'Always push notification' ),
			'push_if'        => $this->trans( 'Push notification if' ),
			'do_not_push_if' => $this->trans( 'Do not push notification if' ),
		);
	}

	public function getConditionals(): array {
		return array(
			'is_equal_to'               => $this->trans( 'is equal to' ),
			'is_not_equal_to'           => $this->trans( 'is not equal to' ),
			'is_empty'                  => $this->trans( 'is empty' ),
			'is_not_empty'              => $this->trans( 'is not empty' ),
			'contains'                  => $this->trans( 'contains' ),
			'does_not_contain'          => $this->trans( 'doesn’t contain' ),
			'match_regular_expressions' => $this->trans( 'match regular expressions' ),
			'is_less_than'              => $this->trans( 'is less than' ),
			'is_less_or_equal_to'       => $this->trans( 'is less or equal to' ),
			'is_greater_than'           => $this->trans( 'is greater than' ),
			'is_greater_or_equal_to'    => $this->trans( 'is greater or equal to' ),
		);
	}

	public function callback_some( $or_data ): bool {
		return Functions::array_every( array( $this, 'callback_every' ), $or_data );
	}

	public function callback_every( $and_data ): bool {
		$conditionals = $this->getConditionals();

		if ( ! isset( $and_data['operator'] ) ) {
			error_log( 'The operator not exist in: ' . json_encode( $and_data ) );

			return false;
		}

		$operator = $and_data['operator'];

		if ( ! isset( $conditionals[ $operator ] ) ) {
			error_log( "The $operator not validate" );

			return false;
		}

		$value1 = $this->replaceTags( $and_data['value1'] );
		$value2 = $this->replaceTags( $and_data['value2'] );

		return Functions::operators( $operator, $value1, $value2 );
	}

	/**
	 * Check conditionals before push notifications
	 *
	 * @return bool
	 */
	public function checkConditionals(): bool {

		$data_when_conditionals = $this->getDataWhenConditionals();
		$data_conditionals      = $this->getDataConditionals();

		if ( $data_when_conditionals == 'always' || count( $data_conditionals ) == 0 ) {
			return true;
		}

		$result = Functions::array_some( array( $this, 'callback_some' ), $data_conditionals );

		return $data_when_conditionals == 'push_if' ? $result : ! $result;
	}

	/**
	 * Prepare Recipients
	 *
	 * @param $recipients
	 *
	 * @return mixed
	 */
	public function prepare_recipients( $recipients ) {
		return $recipients;
	}

	/**
	 * Push notification to recipients
	 */
	public function push() {

		$message    = $this->getMessage();
		$recipients = $this->getRecipients();

		$message->setData( $this->navigate() );

		if ( ! empty( $message ) && count( $recipients ) > 0 && $this->checkConditionals() ) {
			pushNotify()->push( new NotificationJob( $this->prepare_recipients( $recipients ), $message ) );
		}
	}

	/**
	 * Merge tags for the notification message
	 *
	 * @return array
	 */
	public function mergeTags(): array {
		return array();
	}

	/**
	 * Get default form settings
	 *
	 * @return array
	 */
	public function getForm(): array {
		return array(
			'name'              => $this->getName(),
			'group'             => $this->getGroup(),
			'type'              => $this->getType(),
			'title'             => $this->getTitle(),
			'body'              => $this->getBody(),
			'variables'         => $this->mergeTags(),
			'recipients'        => $this->getRecipients(),
			'when_conditionals' => $this->whenConditionals(),
			'conditionals'      => $this->getConditionals(),
			'info'              => $this->getDefaultInfo(),
			'status'            => true,
		);
	}
}
