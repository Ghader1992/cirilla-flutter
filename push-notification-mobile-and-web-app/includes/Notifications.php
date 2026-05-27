<?php

namespace PushNotify;

use PushNotify\Notifications\Wc\ProductStatusChanged;
use PushNotify\Notifications\Wp\Comment;
use PushNotify\Notifications\Wc\OrderStatusChanged;
use PushNotify\Notifications\Wp\Post;
use PushNotify\Notifications\Wp\SavePost;
use PushNotify\Notifications\Wcfm\AfterWcfmNotification;

use PushNotify\Notifications\BuddyPress\MessagesMessageSent;
use PushNotify\Notifications\BuddyPress\ActivityPostedUpdate;
use PushNotify\Notifications\BuddyPress\GroupsPostedUpdate;
use PushNotify\Notifications\BuddyPress\FriendsFriendshipAccepted;
use PushNotify\Notifications\BuddyPress\FriendsFriendshipRequested;
use PushNotify\Notifications\BuddyPress\GroupsSendInvites;

/**
 * Class Setting
 *
 * @author ngocdt@rnlab.io
 * @since 1.0.0
 */
class Notifications extends Base {

	/**
	 * Get groups message setting
	 *
	 * @return array[]
	 */
	public function get_groups(): array {

		$groups = array(
			'wp' => array(
				'title'       => $this->trans( 'Wordpress' ),
				'status'      => true,
				'description' => '',
				'actions'     => array(
					'comment'   => array(
						'title'       => $this->trans( 'Comment Post' ),
						'description' => $this->trans( 'Fires immediately after a comment is inserted into the database.' ),
					),
					'post'      => array(
						'title'       => $this->trans( 'Post Type' ),
						'description' => $this->trans( 'Fires when a post is transitioned from one status to another.' ),
					),
					'save_post' => array(
						'title'       => $this->trans( 'Save Post' ),
						'description' => $this->trans( 'Fires once a post has been saved.' ),
					),
				),
			),
		);

		if ( class_exists( 'WooCommerce' ) ) {
			$groups['wc'] = array(
				'title'       => $this->trans( 'Woocomerce' ),
				'status'      => class_exists( 'WooCommerce' ),
				'description' => '',
				'actions'     => array(
					'order_status_changed'   => array(
						'title'       => $this->trans( 'Order Status Changed' ),
						'description' => $this->trans( 'Fires when an order is transitioned from one status to another.' ),
					),
					'product_status_changed' => array(
						'title'       => $this->trans( 'Product Status Changed' ),
						'description' => $this->trans( 'Fires when a product is transitioned from one status to another.' ),
					),
				),

			);
		}

		if ( class_exists( 'WCFMmp' ) ) {
			$groups['wcfm'] = array(
				'title'       => $this->trans( 'WCFM' ),
				'status'      => class_exists( 'WCFMmp' ),
				'description' => '',
				'actions'     => array(
					'after_wcfm_notification' => array(
						'title'       => $this->trans( 'WCFM Sent Message' ),
						'description' => $this->trans( 'Action trigger after WCFM sent a message' ),
					),
				),

			);
		}

		if ( function_exists( 'bp_is_active' ) ) {
			$actions = array(
				'messages_message_sent'            => array(
					'title'       => $this->trans( 'BuddyPress Private messages' ),
					'description' => $this->trans( 'Action trigger after BuddyPress private messages sent' ),
				),
				'bp_activity_posted_update'        => array(
					'title'       => $this->trans( 'Activity Posted Update' ),
					'description' => $this->trans( 'Action trigger after creating activity' ),
				),
				'bp_activity_groups_posted_update' => array(
					'title'       => $this->trans( 'Activity Groups Posted Update' ),
					'description' => $this->trans( 'Action trigger after creating activity under group' ),
				),
				'friends_friendship_accepted'      => array(
					'title'       => $this->trans( 'Friend accepted a friendship request' ),
					'description' => $this->trans( 'Action trigger after friend accepted a friendship request' ),
				),
				'friends_friendship_requested'     => array(
					'title'       => $this->trans( 'Friend send a friendship request' ),
					'description' => $this->trans( 'Action trigger after friend send a friendship request' ),
				),
				'groups_send_invites'              => array(
					'title'       => $this->trans( 'Groups Send Invites' ),
					'description' => $this->trans( 'Action trigger after froups send invites' ),
				),
			);

			if ( class_exists( 'Better_Messages' ) ) {
				$actions['better_messages_message_sent'] = array(
					'title'       => $this->trans( 'Better BuddyPress Private messages' ),
					'description' => $this->trans( 'Action trigger after BuddyPress better private messages sent' ),
				);
			}

			$groups['buddypress'] = array(
				'title'       => $this->trans( 'BuddyPress' ),
				'status'      => function_exists( 'bp_is_active' ),
				'description' => '',
				'actions'     => $actions,
			);
		}

		return $groups;
	}

	/**
	 * Get form notifications
	 *
	 * @return array
	 */
	public function get_actions(): array {

		$comment   = new Comment();
		$post      = new Post();
		$save_post = new SavePost();

		$actions = array(
			'comment'   => $comment->getForm(),
			'post'      => $post->getForm(),
			'save_post' => $save_post->getForm(),
		);

		if ( class_exists( 'WooCommerce' ) ) {
			$order_status_changed              = new OrderStatusChanged();
			$product_status_changed            = new ProductStatusChanged();
			$actions['order_status_changed']   = $order_status_changed->getForm();
			$actions['product_status_changed'] = $product_status_changed->getForm();
		}

		if ( class_exists( 'WCFMmp' ) ) {
			$wcfm                               = new AfterWcfmNotification();
			$actions['after_wcfm_notification'] = $wcfm->getForm();
		}

		if ( function_exists( 'bp_is_active' ) ) {
			$mst                              = new MessagesMessageSent();
			$actions['messages_message_sent'] = $mst->getForm();
			if ( class_exists( 'Better_Messages' ) ) {
				$actions['better_messages_message_sent'] = $mst->getForm();
			}
			$bp_apd                               = new ActivityPostedUpdate();
			$actions['bp_activity_posted_update'] = $bp_apd->getForm();

			$bp_apg                                      = new GroupsPostedUpdate();
			$actions['bp_activity_groups_posted_update'] = $bp_apg->getForm();

			$bp_fa                                  = new FriendsFriendshipAccepted();
			$actions['friends_friendship_accepted'] = $bp_fa->getForm();

			$bp_fr                                   = new FriendsFriendshipRequested();
			$actions['friends_friendship_requested'] = $bp_fr->getForm();

			$bp_gsi                         = new GroupsSendInvites();
			$actions['groups_send_invites'] = $bp_gsi->getForm();
		}

		return $actions;
	}

	/**
	 *
	 * Get custom notification info
	 *
	 * @return array
	 */
	public function getCustomNotificationInfo(): array {
		return array(
			'title'      => array(
				'label'       => $this->trans( 'Notification title' ),
				'placeholder' => $this->trans( 'Enter optional title' ),
				'hint'        => $this->trans( 'Shown to end users as the notification title' ),
				'description' => '',
			),
			'body'       => array(
				'label'       => $this->trans( 'Notification text' ),
				'placeholder' => $this->trans( 'Enter notification text' ),
				'hint'        => '',
				'description' => '',
			),
			'image'      => array(
				'label'       => $this->trans( 'Notification image (optional) ' ),
				'placeholder' => '',
				'hint'        => $this->trans( 'Optionally upload an image or provide a valid HTTPS image URL' ),
				'description' => $this->trans( 'Keep in mind that a 300KB max image size is enforced by the device.' ),
			),
			'sound'        => array(
				'label'       => $this->trans( 'Notification sound (optional) ' ),
				'placeholder' => '',
				'hint'        => $this->trans( 'The sound to play when the device receives the notification.' ),
				'description' => 'String specifying sound files in the main bundle of the client app or in the Library/Sounds folder of the app\'s data container. See the iOS Developer Library for more information.',
			),
			'recipients' => array(
				'label'       => $this->trans( 'Recipients' ),
				'placeholder' => '',
				'hint'        => '',
				'description' => '',
			),
			'action'     => array(
				'label'       => $this->trans( 'Action (optional)' ),
				'placeholder' => '',
				'hint'        => '',
				'description' => '',
			),
		);
	}

	/**
	 * Get sample action
	 *
	 * @return array[]
	 */
	public function getActionSample(): array {
		$data = array(
			array(
				'name'  => 'Home',
				'type'  => 'tab',
				'route' => '/',
				'args'  => array(
					'key'  => 'screens_home',
					'name' => 'Home',
				),
			),
			array(
				'name'  => 'Category',
				'type'  => 'tab',
				'route' => '/',
				'args'  => array(
					'key'  => 'screens_category',
					'name' => 'Category',
				),
			),
			array(
				'name'  => 'Product list',
				'type'  => 'screen',
				'route' => '/product_list',
				'args'  => array(),
			),
			array(
				'name'  => 'Product list filter by category',
				'type'  => 'screen',
				'route' => '/product_list',
				'args'  => array(
					'id'   => '{category_id}',
					'name' => 'Fashion',
				),
				'note'  => '',
			),
			array(
				'name'  => 'Product list with sort',
				'type'  => 'screen',
				'route' => '/product_list',
				'args'  => array(
					'orderby' => 'popularity',
				),
				'note'  => 'Order by one of value: popularity, rating, date, price, price-desc and menu_order',
			),
			array(
				'name'  => 'Product list filter by brand',
				'type'  => 'screen',
				'route' => '/product_list',
				'args'  => array(
					'brand' => '{"id": {brand_id}, "name": "Adidas"}',
				),
				'note'  => 'Order by one of value: popularity, rating, date, price, price-desc and menu_order',
			),
			array(
				'name'  => 'Product detail',
				'type'  => 'screen',
				'route' => '/product',
				'args'  => array(
					'id' => '{id}',
				),
			),
			array(
				'name'  => 'Post List',
				'type'  => 'screen',
				'route' => '/post_list',
				'args'  => array(),
			),
			array(
				'name'  => 'Post detail',
				'type'  => 'screen',
				'route' => '/post',
				'args'  => array(
					'id' => '{id}',
				),
			),
			array(
				'name'  => 'Order detail',
				'type'  => 'screen',
				'route' => '/order_detail',
				'args'  => array(
					'id' => '{order_id}',
				),
			),
			array(
				'name'  => 'Vendor list',
				'type'  => 'tab',
				'route' => '/',
				'args'  => array(
					'key' => 'screens_vendorList',
				),
			),
			array(
				'name'  => 'Vendor detail',
				'type'  => 'screen',
				'route' => '/vendor',
				'args'  => array(
					'id' => '{id}',
				),
			),
			array(
				'name'  => 'Product wish list',
				'type'  => 'tab',
				'route' => '/',
				'args'  => array(
					'key' => 'screens_wishlist',
				),
			),
			array(
				'name'  => 'Post wish list',
				'type'  => 'tab',
				'route' => '/',
				'args'  => array(
					'key' => 'screens_postWishlist',
				),
			),
			array(
				'name'  => 'Post category',
				'type'  => 'tab',
				'route' => '/',
				'args'  => array(
					'key' => 'screens_postCategory',
				),
			),
			array(
				'name'  => 'Profile',
				'type'  => 'tab',
				'route' => '/',
				'args'  => array(
					'key' => 'screens_profile',
				),
			),
			array(
				'name'  => 'Cart',
				'type'  => 'tab',
				'route' => '/',
				'args'  => array(
					'key' => 'screens_cart',
				),
			),
			array(
				'name'  => 'Notification list',
				'type'  => 'screen',
				'route' => '/notification_list',
				'args'  => array(),
			),
			array(
				'name'  => 'Page',
				'type'  => 'screen',
				'route' => '/page',
				'args'  => array(
					'id' => '{id}',
				),
			),
			array(
				'name'  => 'Page',
				'type'  => 'screen',
				'route' => '/page',
				'args'  => array(
					'id' => '{id}',
				),
			),
			array(
				'name'  => 'Login',
				'type'  => 'screen',
				'route' => '/login',
				'args'  => array(),
			),
			array(
				'name'  => 'Register',
				'type'  => 'screen',
				'route' => '/register',
				'args'  => array(),
			),
		);

		return apply_filters( 'push_notify_action_sample', $data );
	}
}
