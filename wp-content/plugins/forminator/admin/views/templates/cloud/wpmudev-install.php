<?php
/**
 * Template admin/views/templates/cloud/wpmudev-install.php
 *
 * @package Forminator
 */

?>
<div class="sui-box sui-message sui-message-lg">
	<img src="<?php echo esc_url( forminator_plugin_url() . 'assets/images/wpmudev-logo.png' ); ?>"
		srcset="<?php echo esc_url( forminator_plugin_url() . 'assets/images/wpmudev-logo.png' ); ?> 1x, <?php echo esc_url( forminator_plugin_url() . 'assets/images/wpmudev-logo@2x.png' ); ?> 2x"
		alt="<?php esc_attr_e( 'WPMU DEV Logo', 'forminator' ); ?>"
		class="sui-image sui-image-center fui-image">
	<div class="sui-message-content">
		<h2><?php esc_html_e( 'Install WPMU DEV Dashboard', 'forminator' ); ?></h2>
		<p>
			<?php esc_html_e( 'You don\'t have the WPMU DEV Dashboard plugin, which you\'ll need to access Pro preset templates. Install and log in to the dashboard to unlock the complete list of preset templates.', 'forminator' ); ?>
		</p>
		<p>
			<a href="https://wpmudev.com/project/wpmu-dev-dashboard/" target="_blank" class="sui-button sui-button-icon-left sui-button-blue">
				<span class="sui-icon-wpmudev-logo" aria-hidden="true"></span>
				<?php esc_html_e( 'Install Plugin', 'forminator' ); ?>
			</a>
		</p>
	</div>
</div>
