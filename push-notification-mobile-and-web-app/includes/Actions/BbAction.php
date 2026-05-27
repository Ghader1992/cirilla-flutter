<?php
namespace PushNotify\Actions;

use PushNotify\Notifications\BuddyPress\MessagesMessageSent;
use PushNotify\Notifications\BuddyPress\ActivityPostedUpdate;
use PushNotify\Notifications\BuddyPress\GroupsPostedUpdate;
use PushNotify\Notifications\BuddyPress\FriendsFriendshipAccepted;
use PushNotify\Notifications\BuddyPress\FriendsFriendshipRequested;
use PushNotify\Notifications\BuddyPress\GroupsSendInvites;

/**
 * Class BbAction
 *
 * @link       https://appcheap.io/docs/push-notification-documentations/
 * @author     ngocdt
 * @since      1.5.0
 */
class BbAction {
	/**
	 * BbAction constructor .
	 */
	public function __construct() {
		add_action( 'messages_message_sent', array( $this, 'messages_message_sent' ), 10 );
		if ( class_exists( 'Better_Messages' ) ) {
			add_action( 'better_messages_message_sent', array( $this, 'messages_message_sent' ), 10 );
		}
		add_action( 'bp_activity_posted_update', array( $this, 'bp_activity_posted_update' ), 10, 3 );
		add_action( 'bp_activity_groups_posted_update', array( $this, 'bp_activity_groups_posted_update' ), 10, 4 );
		add_action( 'friends_friendship_accepted', array( $this, 'friends_friendship_accepted' ), 10, 3 );
		add_action( 'friends_friendship_requested', array( $this, 'friends_friendship_requested' ), 10, 3 );
		add_action( 'groups_send_invites', array( $this, 'groups_send_invites' ), 10, 3 );
	}

	/**
	 * Triggered when a group invite is sent in BuddyPress.
	 *
	 * @param int   $group_id The ID of the group being invited to.
	 * @param array $invited_users Array of user IDs being invited to the group.
	 * @param int   $inviter_id The ID of the user who sent the invite.
	 */
	public function groups_send_invites( $group_id, $invited_users, $inviter_id ) {
		$notifications = pushNotify()->settings()->notifications();
		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && 'groups_send_invites' === $value['type'] && $value['status'] ) {
				$obj = new GroupsSendInvites();
				$obj->setGroupId( $group_id )
					->setInvitedUsers( $invited_users )
					->setInviterId( $inviter_id )
					->setId( $key )
					->push();
			}
		}
	}

	/**
	 * Triggered when a friendship is accepted in BuddyPress.
	 *
	 * @param int $friendship_id The friendship ID.
	 * @param int $initiator_id The ID of the user who initiated the friendship.
	 * @param int $friend_id The ID of the user who accepted the friendship.
	 */
	public function friends_friendship_accepted( $friendship_id, $initiator_id, $friend_id ) {
		$notifications = pushNotify()->settings()->notifications();
		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && 'friends_friendship_accepted' === $value['type'] && $value['status'] ) {
				$obj = new FriendsFriendshipAccepted();
				$obj->setFriendshipId( $friendship_id )
					->setInitiatorId( $initiator_id )
					->setFriendId( $friend_id )
					->setId( $key )
					->push();
			}
		}
	}

	/**
	 * Triggered when a friendship is requested in BuddyPress.
	 *
	 * @param int $friendship_id The friendship ID.
	 * @param int $initiator_id The ID of the user who initiated the friendship.
	 * @param int $friend_id The ID of the user who accepted the friendship.
	 */
	public function friends_friendship_requested( $friendship_id, $initiator_id, $friend_id ) {
		$notifications = pushNotify()->settings()->notifications();
		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && 'friends_friendship_requested' === $value['type'] && $value['status'] ) {
				$obj = new FriendsFriendshipRequested();
				$obj->setFriendshipId( $friendship_id )
					->setInitiatorId( $initiator_id )
					->setFriendId( $friend_id )
					->setId( $key )
					->push();
			}
		}
	}

	/**
	 * Triggered when a message is sent in BuddyPress.
	 *
	 * @param object $message The message object.
	 */
	public function messages_message_sent( object $message ) {
		if ( ! empty( $message->recipients ) ) {
			$notifications = pushNotify()->settings()->notifications();
			foreach ( $notifications as $key => $value ) {
				if ( isset( $value['type'] ) && $this->allows( $value['type'] ) && $value['status'] ) {
					$obj = new MessagesMessageSent();
					$obj->setMessage( $message )
						->setId( $key )
						->push();
				}
			}
		}
	}

	/**
	 * Triggered when an activity is posted in BuddyPress.
	 *
	 * @param string $content The content of the activity.
	 * @param int    $user_id The ID of the user who posted the activity.
	 * @param int    $activity_id The ID of the activity.
	 */
	public function bp_activity_posted_update( $content, $user_id, $activity_id ) {
		$notifications = pushNotify()->settings()->notifications();
		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && 'bp_activity_posted_update' === $value['type'] && $value['status'] ) {
				$obj = new ActivityPostedUpdate();
				$obj->setContent( $content )
					->setUserId( $user_id )
					->setActivityId( $activity_id )
					->setId( $key )
					->push();
			}
		}
	}

	/**
	 * Trigger after creating activity under group
	 *
	 * @param string $content The content of the activity.
	 * @param int    $user_id The ID of the user who posted the activity.
	 * @param int    $group_id The ID of the group.
	 * @param int    $activity_id The ID of the activity.
	 */
	public function bp_activity_groups_posted_update( $content, $user_id, $group_id, $activity_id ) {
		$notifications = pushNotify()->settings()->notifications();
		foreach ( $notifications as $key => $value ) {
			if ( isset( $value['type'] ) && 'bp_activity_groups_posted_update' === $value['type'] && $value['status'] ) {
				$obj = new GroupsPostedUpdate();
				$obj->setContent( $content )
					->setUserId( $user_id )
					->setActivityId( $activity_id )
					->setGroupId( $group_id )
					->setId( $key )
					->push();
			}
		}
	}


	/**
	 * Check if the given type is allowed.
	 *
	 * @param string $type The type to check.
	 *
	 * @return bool True if the type is allowed, false otherwise.
	 */
	public function allows( string $type ): bool {
		return 'messages_message_sent' === $type || 'better_messages_message_sent' === $type;
	}
}
