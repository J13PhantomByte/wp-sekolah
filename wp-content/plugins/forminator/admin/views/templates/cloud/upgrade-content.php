<?php
/**
 * Template admin/views/templates/cloud/upgrade-content.php
 *
 * @package Forminator
 */

?>
<div class="sui-box sui-message sui-message-lg">
	<img src="<?php echo esc_url( forminator_plugin_url() . 'assets/images/forminator-templates.png' ); ?>"
		srcset="<?php echo esc_url( forminator_plugin_url() . 'assets/images/forminator-templates.png' ); ?> 1x, <?php echo esc_url( forminator_plugin_url() . 'assets/images/forminator-templates@2x.png' ); ?> 2x"
		alt="<?php esc_attr_e( 'Forminator no result', 'forminator' ); ?>"
		class="sui-image sui-image-center fui-image">
	<div class="sui-message-content">
		<h2><?php esc_html_e( 'Save Forms as Templates', 'forminator' ); ?></h2>
		<p>
			<?php esc_html_e( 'Save your forms as templates in the Hub cloud to easily reuse them on any sites you manage via the Hub. Customize once and reuse on different sites with one click.', 'forminator' ); ?>
		</p>
		<p>
			<a href="https://wpmudev.com/project/forminator-pro/?utm_source=forminator&utm_medium=plugin&utm_campaign=forminator_template-page_cloud-template_button" target="_blank" class="sui-button sui-button-icon-right sui-button-purple">
				<?php esc_html_e( 'Upgrade to Save Template', 'forminator' ); ?>
				<span class="sui-icon-open-new-window" aria-hidden="true"></span>
			</a>
		</p>
	</div>
</div>
