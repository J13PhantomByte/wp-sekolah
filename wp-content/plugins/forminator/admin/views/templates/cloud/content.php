<?php
/**
 * Template admin/views/templates/cloud/content.php
 *
 * @package Forminator
 */

?>
<div
	role="tabpanel"
	tabindex="0"
	id="cloud-templates-content"
	class="sui-tab-content"
	aria-labelledby="cloud-templates"
>
	<!-- Pro saved template box -->
	<div class="sui-box">
		<?php
		if ( ! FORMINATOR_PRO ) {
			echo forminator_template( 'templates/cloud/upgrade-content' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( ! class_exists( 'WPMUDEV_Dashboard' ) ) {
			echo forminator_template( 'templates/cloud/wpmudev-install' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( ! WPMUDEV_Dashboard::$api->get_key() ) {
			echo forminator_template( 'templates/cloud/wpmudev-login' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( 'expired' === forminator_get_wpmudev_membership() ) {
			echo forminator_template( 'templates/cloud/wpmudev-renew' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo forminator_template( 'templates/cloud/listings' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo forminator_template( 'templates/cloud/empty-content' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</div>
</div>
