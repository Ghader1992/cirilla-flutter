=== Push notification for Mobile and Web app ===
Contributors: appcheap, rnlab
Donate link: https://1.envato.market/x9JBRR
Tags: push notification, android notifications, ios notifications, app builder, firebase messages, push notifications, onesignal, web notifications, push, notification, notifications, notify, web push
Requires at least: 5.8
Tested up to: 6.6.2
Stable tag: 2.0.4
License: GPL v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.4

Push notification for Android, iOS and the Web

== Description ==

Support push notification for mobile and the web app.

[Demo app](https://codecanyon.net/item/cirilla-multipurpose-flutter-wordpress-app/31940668)

=== Push services support ===

- Firebase HTTP V1
- Firebase HTTP legacy
- OneSignal
- Debug

=== How does it work ===

The Push Notification plugin is built with five part:

- Trigger: When WordPress action execution (Post saved, Order status changed ...)
- Recipients: One/ More recipients get the notification ( topic, registration ID, role, user, merge tag ...)
- Conditionals: Determine whether notification send
- Action: The action when the user click to notification on device
- Merge Tag: That is dynamic information in that context
- String translation: Replace part of string on title and message

=== Plugin Features ===

- Comment Post: Fires immediately after a comment is inserted into the database.
- Post Type: Fires when a post is transitioned from one status to another.
- Save Post: Fires once a post has been saved.
- Order Status Changed: Fires when an order is transitioned from one status to another.
- Product Status Changed: Fires when a product is transitioned from one status to another.
- WCFM – Direct Messaging: Fires when vendor receive a message.
- BuddyPress: Fires Messages message sent, Activity Posted Update, Friends Friendship Accepted, Friends Friendship Requested, Groups Posted Update, Groups Send Invites

== Installation ==

1. Install using the WordPress built-in Plugin installer, or Extract the zip file and drop the contents in the `wp-content/plugins/` directory of your WordPress installation.
2. Activate the plugin through the 'Plugins' menu in WordPress.
4. Press the 'Configure' button.

== Upgrade Notice ==

Read carefully changelogs before upgrade plugin.

== Frequently Asked Questions ==

== Screenshots ==

1. screenshot-1.jpg
2. screenshot-2.jpg
3. How Push Notification Work

== Changelog ==

= 2.0.4
* Upgrade: Enhance security by using a App Builder Key in the API header.

= 2.0.3
* Fixed: Notification sound for ios

= 2.0.2
* Fixed: Missing Job.php file

= 2.0.0
* Feat: Cached access token and expired time 
* Feat: Support php from 7.4
* Breaking changed: Remove Google client dependecy
* Breaking changed: Remove WP_Queue

= 1.9.2
* Feature: Get variable from post object or terms of the taxonomy that are attached to the post type ex: {object.job_listing_category property:term_id prefix:cat-} => ["cat-1", "cat-2", "cat-3"]

= 1.9.1
* Feature: Variable list object can transform to list string ex: categories [{"term_id", 1}, {"term_id", 1}] => ["cat-1", "cat-2", "cat-3" ] by this syntax {categories property:term_id prefix:cat-}
* Support: Recipient type topic accept a list ex: ["topic1", "topic2", "topic3" ]

= 1.8.7
* Improved: Remove HTML tags from the notification content

= 1.8.6
* Fixed: Get missing post type

= 1.8.5
* Requires PHP: 8.1
* Requires at least: 5.8

= 1.8.4
* Fix: Send merge tag
* Fix: send to user

= 1.8.3
* Fix: Send notification

= 1.8.1
* Support: Firebase Cloud Messaging HTTP v1

= 1.7.1
* Support: Send notification Firebase with sound
* Support: Send notification OneSignal with sound
* Support: Send notification Onesignal with image

= 1.7.0
* Support: OneSignal Push Notification

= 1.6.3 =
* Chore: add send to initiator_id

= 1.6.2 =
* Chore: Update feature list

= 1.6.1 =
* Fixed: BuddyPress - Bester messages message sent

= 1.6.0 =
* Add: BuddyPress - Bester messages message sent
* Add: BuddyPress - A member sends you a friendship request
* Add: BuddyPress - A member accepts your friendship request
* Add: BuddyPress - A member replies to an update or comment you’ve posted
* Add: BuddyPress - A member mentions you in an update @username”
* Add: BuddyPress - A member invites you to join a group

= 1.5.0 =
* Add: BuddyPress - messages_message_sent

= 1.4.5 =
* Add: Action after WCFM message

= 1.4.4 =
* Fixed: Encode action data

= 1.4.3 =
* Add: Sample action cart, wishlist, profile, vendor, ...
* Fixed: Upload file syntax php 8 to WordPress plugin server

= 1.4.2 =
* Add: String translate notification
* Fixed: click to notification

= 1.4.1 =
* Add: Sample action to order
* Add: Action filter push_notify_action_sample

= 1.4.0 =
* Add: Push notification to roles
* Add: Push notification to Email/Merge tag
* Add: Push notification to topic
* Add: Push notification with image
* Add: Notification with Conditionals

= 1.2.0 =
* Add: Send custom notification
* Add: API deleted all notifications
* Add: API get notification by user id
* Add: API delete notification by user id
* Add: API count notification
* Add: Update notification status
* Improved: UI & UX
* Improved: Send message under background

= 1.1.0 =
* Add: API get tokens by userId
* Fixed: Send notification when order status changed
* Fixed: Remove token expired
* Chose: Update link settings

= 1.0.4 =
* Add: User hooks

= 1.0.3 =
* Chose: Test up WordPress 5.8.1

= 1.0.1 =
* Chose: Update attribute.

= 1.0.0 =
* Initial release.