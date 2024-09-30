<?php
/**
 * Template admin/views/templates/banner/wpmudev-login.php
 *
 * @package Forminator
 */

?>
<div class="sui-box forminator-banner">
	<div class="sui-box forminator-banner-content">
		<div>
			<img src="<?php echo esc_url( forminator_plugin_url() . 'assets/images/wpmudev-logo.png' ); ?>"
				srcset="<?php echo esc_url( forminator_plugin_url() . 'assets/images/wpmudev-logo.png' ); ?> 1x, <?php echo esc_url( forminator_plugin_url() . 'assets/images/wpmudev-logo@2x.png' ); ?> 2x"
				alt="<?php esc_attr_e( 'WPMU DEV Logo', 'forminator' ); ?>"
				class="sui-image sui-image-center fui-image">
		</div>
		<div>
			<h2><?php esc_html_e( 'Log In to Your WPMU DEV Account', 'forminator' ); ?></h2>
			<p>
				<?php esc_html_e( 'Looks like your site is not connected to WPMU DEV. Log in to your WPMU DEV account to unlock the complete list of preset templates.', 'forminator' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=wpmudev' ) ); ?>" target="_blank" class="sui-button sui-button-icon-left sui-button-blue">
					<span class="sui-icon-wpmudev-logo" aria-hidden="true"></span>
					<?php esc_html_e( 'Log in to WPMU DEV', 'forminator' ); ?>
				</a>
			</p>
		</div>
	</div>
</div>
