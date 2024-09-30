<?php
/**
 * Template admin/views/settings/tab-tracking.php
 *
 * @package Forminator
 */

$forminator_usage_tracking = get_option( 'forminator_usage_tracking', false );
?>
<div class="sui-box-settings-row">

	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Usage Tracking', 'forminator' ); ?></span>
		<span class="sui-description">
			<?php
			printf(
				/* translators: 1: Anchor tag with forminator data tracking doc URL, 2: Close anchor tag */
				esc_html__( 'Help us improve Forminator by sharing anonymous and non-sensitive usage data. See %1$smore info%2$s about the data we collect', 'forminator' ),
				'<a href="https://wpmudev.com/docs/privacy/our-plugins/#usage-tracking-fm" target="_blank">',
				'</a>'
			);
			?>
		</span>
	</div>

	<div class="sui-box-settings-col-2">

		<label for="forminator-usage-tracking" class="sui-toggle">
			<input type="checkbox"
				name="usage_tracking"
				value="true"
				id="forminator-usage-tracking" <?php checked( $forminator_usage_tracking, 1 ); ?>/>
			<span class="sui-toggle-slider" aria-hidden="true"></span>
			<span class="sui-toggle-label"><?php esc_html_e( 'Allow usage tracking', 'forminator' ); ?></span>
		</label>
		<p class="sui-description">
			<?php esc_html_e( 'Note: Usage tracking is completely anonymous and non-sensitive, and we only track features you are/aren\'t using to make more informed feature decisions.', 'forminator' ); ?>
		</p>

	</div>

</div>
