<?php
/**
 * Merge tag metabox template
 *
 * @package notification
 */


?>

<div class="notification_merge_tags_accordion">
	<?php foreach ($tags as $code => $tag): ?>
		<div class="intro" style="padding: 10px 0;">
			<label style="display: block; margin-bottom: 5px;"><?= $tag ?></label>
			<code class="notification-merge-tag">{<?= $code ?>}</code>
		</div>
	<?php endforeach; ?>
</div>
