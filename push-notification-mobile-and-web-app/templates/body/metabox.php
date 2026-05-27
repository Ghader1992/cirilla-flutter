<?php
/**
 * Trigger metabox template
 *
 * @package PushNotify
 */

$roles = pushNotify()->settings()->datas()['roles'];
?>

<h3 class="trigger-section-title"><?php esc_html_e( 'Main Content', PUSH_NOTIFY_DOMAIN ); ?></h3>

<div class="body-content">
	<div class="form-group">
		<label for="status"><?php esc_html_e( 'Enable Notification', PUSH_NOTIFY_DOMAIN ); ?></label>
		<input type="checkbox" id="status" value="true" <?= $status ? 'checked' : ''; ?> name="status">
	</div>

	<div class="form-group">
		<label for="subject"><?php esc_html_e( 'Subject', PUSH_NOTIFY_DOMAIN ); ?></label>
		<input type="text" id="subject" value="<?php echo $subject; ?>" style="width: 100%;" name="subject">
	</div>
	<div class="form-group">
		<label for="body"><?php esc_html_e( 'Body', PUSH_NOTIFY_DOMAIN ); ?></label>
		<?php echo wp_editor( $body, 'body' ); ?>
	</div>
	<div class="form-group">
		<label class="recipients_notification"><?php esc_html_e( 'Recipients', PUSH_NOTIFY_DOMAIN ); ?></label>
		<table>
			<tr>
				<th>Type</th>
				<th>Recipient</th>
			</tr>
			
			<?php if ($recipients): ?>
				<?php 
					foreach ($recipients as $key => $recipient) {
						switch ($key) {
							case 'token':
								foreach ($recipient as $value) {
									?>
										<tr>
											<td>Token ID</td>
											<td><input type="text" name="recipients[token][]" value="<?= $value; ?>" placeholder="Token ID" /></td>
										</tr>
									<?php
								}
								break;

							case 'role':
								foreach ($recipient as $value) {
									?>
										<tr>
											<td>Role</td>
											<td>
												<select name="recipients[role][]" id="recipients_role">
												<?php foreach ($roles as $key => $role): ?>
													<option value="<?= $key; ?>" <?= $value == $key ? 'selected' : ''; ?>><?= $role; ?></option>
												<?php endforeach; ?>
												</select>
											</td>
										</tr>
									<?php
								}
								break;
							
							default:
								break;
						}
					}
				?>
			<?php else: ?>
				<tr>
					<td>Token ID</td>
					<td><input type="text" name="recipients[token][]" placeholder="Token ID" /></td>
				</tr>
				<tr>
					<td>Role</td>
					<td>
						<select name="recipients[role][]" id="recipients_role">
							<?php foreach ($roles as $key => $role): ?>
								<option value="<?= $key; ?>"><?= $role; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			<?php endif; ?>
		</table>
	</div>
</div>
