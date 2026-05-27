<?php
/**
 * Trigger metabox template
 *
 * @package PushNotify
 */

$triggers = pushNotify()->settings()->datas()['triggers'];
?>

<h3 class="trigger-section-title"><?php esc_html_e( 'Trigger', PUSH_NOTIFY_DOMAIN ); ?></h3>

<select id="trigger" name="trigger" class="pretty-select" data-placeholder="<?php esc_attr_e( 'Select trigger', PUSH_NOTIFY_DOMAIN ); ?>">

	<?php foreach ( $triggers as $group => $subtriggers ) : ?>

	<optgroup label="<?php echo $group; ?>">

		<?php foreach ( $subtriggers as $key => $subtrigger ) : ?>

			<option value="<?php echo $key; ?>" <?php echo $currentTrigger === $key ? 'selected' : ''; ?>><?php echo $subtrigger; ?></option>

		<?php endforeach; ?>

	</optgroup>

	<?php endforeach; ?>

</select>
