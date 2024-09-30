<?php
/**
 * Template admin/views/templates/cloud/empty-content.php
 *
 * @package Forminator
 */

?>
<div class="sui-box sui-message sui-message-lg" id="no-templates" style="display: none;">
	<img src="<?php echo esc_url( forminator_plugin_url() . 'assets/images/forminator-no-result.png' ); ?>"
		srcset="<?php echo esc_url( forminator_plugin_url() . 'assets/images/forminator-no-result.png' ); ?> 1x, <?php echo esc_url( forminator_plugin_url() . 'assets/images/forminator-no-result@2x.png' ); ?> 2x"
		alt="<?php esc_attr_e( 'Forminator no result', 'forminator' ); ?>"
		class="sui-image sui-image-center fui-image">
	<div class="sui-message-content">
		<h2><?php esc_html_e( 'No templates available', 'forminator' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: %1$s - opening anchor tag, %2$s - closing anchor tag */
				esc_html__( 'You have not saved any form templates yet. All your saved form templates will be displayed here. Click %1$shere%2$s to learn more on how to create form templates.', 'forminator' ),
				'<a href="https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#templates" target="_blank">',
				'</a>'
			);
			?>
		</p>
	</div>
</div>
