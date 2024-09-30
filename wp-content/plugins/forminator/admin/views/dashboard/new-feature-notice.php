<?php
/**
 * Template admin/views/dashboard/new-feature-notice.php
 *
 * @package Forminator
 */

$user      = wp_get_current_user();
$banner_1x = forminator_plugin_url() . 'assets/images/Feature_highlight.png';
$banner_2x = forminator_plugin_url() . 'assets/images/Feature_highlight@2x.png';
?>

<div class="sui-modal sui-modal-md">

	<div
		role="dialog"
		id="forminator-new-feature"
		class="sui-modal-content"
		aria-live="polite"
		aria-modal="true"
		aria-labelledby="forminator-new-feature__title"
	>

		<div class="sui-box forminator-feature-modal" data-prop="forminator_dismiss_feature_1320" data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_dismiss_notification' ) ); ?>">

			<div class="sui-box-header sui-flatten sui-content-center">

				<figure class="sui-box-banner" aria-hidden="true">
					<img
						src="<?php echo esc_url( $banner_1x ); ?>"
						srcset="<?php echo esc_url( $banner_1x ); ?> 1x, <?php echo esc_url( $banner_2x ); ?> 2x"
						alt=""
					/>
				</figure>

				<button class="sui-button-icon sui-button-white sui-button-float--right forminator-dismiss-new-feature" data-type="dismiss" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog.', 'forminator' ); ?></span>
				</button>

				<h3 class="sui-box-title sui-lg" style="overflow: initial; white-space: initial; text-overflow: initial;">
					<?php esc_html_e( 'New: Preset Templates and Rating Field', 'forminator' ); ?>
				</h3>

				<p class="sui-description">
					<?php
					printf(
						/* translators: 1. Admin name 2. Open b tag, 3. Close b tag */
						esc_html__( 'Hey %1$s, we\'re excited to introduce our latest additions to Forminator: %2$sPreset Templates (Pro)%3$s, %2$sRating Field%3$s, and %2$sEnhanced Multi-Select UI%3$s. These new additions will speed up your form creation process and enhance the functionality of your forms.', 'forminator' ),
						esc_html( ucfirst( $user->display_name ) ),
						'<b>',
						'</b>'
					);
					?>
				</p>

				<div class="sui-modal-list">
					<ul style="text-align: left;">

						<li>
							<h3 style="margin-bottom: 0;">
								<span class="sui-icon-check sui-sm" aria-hidden="true"></span>
								&nbsp;&nbsp;
								<?php esc_html_e( 'Preset and Cloud Templates', 'forminator' ); ?>
								<span class="sui-tag sui-tag-sm sui-tag-purple">Pro</span>
							</h3>
							<p class="sui-description" style="margin-left: 24px; margin-bottom: 30px;">
								<?php
								printf(
									/* translators: 1. Open link tag, 2. Close link tag */
									esc_html__( 'With our pre-designed templates, you can easily create forms for different purposes. You can also create custom form templates, save them to the cloud and reuse them on any sites you manage via the Hub. %1$sLearn more%2$s', 'forminator' ),
									'<a href="' . esc_url( 'https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#templates' ) . '" target="_blank">',
									' <span class="sui-icon-open-new-window" aria-hidden="true"></span></a>'
								);
								?>
							</p>
						</li>

						<li>
							<h3 style="margin-bottom: 0;">
								<span class="sui-icon-check sui-sm" aria-hidden="true"></span>
								&nbsp;&nbsp;
								<?php esc_html_e( 'Rating Field', 'forminator' ); ?>
							</h3>
							<p class="sui-description" style="margin-left: 24px; margin-bottom: 30px;">
								<?php
								printf(
									/* translators: 1. Open link tag, 2. Close link tag */
									esc_html__( 'With the new Rating field, you can now add star ratings to your forms and allow users to submit their feedback on your products, services, ideas etc. %1$sLearn more%2$s', 'forminator' ),
									'<a href="' . esc_url( 'https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#rating-field' ) . '" target="_blank">',
									' <span class="sui-icon-open-new-window" aria-hidden="true"></span></a>'
								);
								?>
							</p>
						</li>

						<li>
							<h3 style="margin-bottom: 0;">
								<span class="sui-icon-check sui-sm" aria-hidden="true"></span>
								&nbsp;&nbsp;
								<?php esc_html_e( 'Enhanced UI for Multi-Select', 'forminator' ); ?></h3>
							<p class="sui-description" style="margin-left: 24px;">
								<?php
								printf(
									/* translators: 1. Open link tag, 2. Close link tag */
									esc_html__( 'We’ve improved our Select field to support modern multi-selection styles and tags, creating a better experience for form users. %1$sCheck it out%2$s', 'forminator' ),
									'<a href="' . esc_url( 'https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#multi-select' ) . '" target="_blank">',
									' <span class="sui-icon-open-new-window" aria-hidden="true"></span></a>'
								);
								?>
							</p>
						</li>

					</ul>
				</div>

			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">

				<button class="sui-button forminator-dismiss-new-feature" data-modal-close>
					<?php esc_html_e( 'Got it!', 'forminator' ); ?>
				</button>

			</div>

		</div>

	</div>

</div>

<script type="text/javascript">
	jQuery('#forminator-new-feature .forminator-dismiss-new-feature').on('click', function (e) {
	e.preventDefault()

	var $notice = jQuery(e.currentTarget).closest('.forminator-feature-modal'),
		ajaxUrl = '<?php echo esc_url( forminator_ajax_url() ); ?>',
		dataType = jQuery(this).data('type'),
		ajaxData = {
		action: 'forminator_dismiss_notification',
		prop: $notice.data('prop'),
		_ajax_nonce: $notice.data('nonce')
		}

	if ( 'save' === dataType ) {
		ajaxData['usage_value'] = jQuery('#forminator-new-feature-toggle').is(':checked')
	}

	jQuery.post(ajaxUrl, ajaxData)
		.always(function () {
		$notice.hide()
		})
	})
</script>
