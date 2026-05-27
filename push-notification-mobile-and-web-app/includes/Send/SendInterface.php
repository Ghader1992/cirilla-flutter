<?php

namespace PushNotify\Send;

/**
 * Interface SendInterface
 *
 * @package PushNotify\Send
 */

use PushNotify\Message\Message;

interface SendInterface {
	/**
	 * Pushes a message to the intended recipients.
	 *
	 * @return void
	 */
	public function push(): void;

	/**
	 * Pushes a message to the intended recipients with the given registration IDs.
	 *
	 * @param array $tokens The registration IDs of the intended recipients.
	 *
	 * @return void
	 */
	public function push_to_registration_ids( array $tokens ): void;

	/**
	 * Pushes a message to the intended recipients with the given user IDs.
	 *
	 * @param array $users The user IDs of the intended recipients.
	 *
	 * @return void
	 */
	public function push_to_users( array $users ): void;

	/**
	 * Pushes a message to the intended recipients with the given roles.
	 *
	 * @param array $roles The roles of the intended recipients.
	 *
	 * @return void
	 */
	public function push_to_roles( array $roles ): void;

	/**
	 * Pushes a message to the intended recipients subscribed to the given topic.
	 *
	 * @param string $topic The topic to which the recipients are subscribed.
	 *
	 * @return void
	 */
	public function push_to_topic( string $topic ): void;

	/**
	 * Pushes a message to the intended recipient with the given email.
	 *
	 * @param string $email The email of the intended recipient.
	 *
	 * @return void
	 */
	public function push_to_email( string $email ): void;
}
