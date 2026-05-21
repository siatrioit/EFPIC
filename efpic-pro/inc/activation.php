<?php
/**
 * Handle plugin activation
 * 
 * @since 1.0.0
 */
function efpic_pro_activation_handler() {
	$activation_mode = get_option( '_efpic_pro_activation_mode' );

	if ( $activation_mode ) {
		add_filter( 'admin_notices', function() {
			?>
			<div class="notice efpic-activtion-notice">
				<form action="<?php echo admin_url( 'admin.php?page=efpic-pro' ); ?>" method="post">
					<?php wp_nonce_field( 'efpic_deactivate_old_modules', 'efpic_activation' ); ?>
					<p><img class="efpic-logo" src="<?php echo EFPIC_PRO_URL; ?>assets/efpic-logo-light.png" alt="efpic" /></p>
					<p><?php /* translators: Part of an admin notification */ _e( 'Thank you for installing <strong>efpic Pro</strong>!', 'efpic-pro' ); ?> 🥳
					<?php /* translators: Part of an admin notification */ _e( 'To use it, the old Pro modules must be deactivated.', 'efpic-pro' ); ?> <a href="https://efpic.io/docs/pro/new-pro-plugin-faqs/"><?php /* translators: Link text */ _e( 'Learn more…', 'efpic-pro') ;?></a></p>
					<p><button class="button button-primary" type="submit"><?php /* translators: Button text */ _e( 'Deactivate &amp; delete old Pro modules', 'efpic-pro' ); ?></button></p>
				</form>
			</div>
		<?php
		});
	}
}

add_action( 'admin_init', 'efpic_pro_activation_handler', 12 );


/**
 * Deactivate old Pro modules
 * 
 * @since 1.0.0
 */
function efpic_pro_deactivate_old_modules() {

	if ( isset( $_POST['efpic_activation'] ) && wp_verify_nonce( $_POST['efpic_activation'], 'efpic_deactivate_old_modules' ) ) {

		$pro_modules = efpic_get_old_pro_modules();

		// Deactivate, if active
		foreach( $pro_modules as $pro_module ) {
			if ( is_plugin_active( $pro_module ) ) {
				deactivate_plugins( $pro_module, true );
			}
		}

		// Delete Pro modules
		delete_plugins( $pro_modules );

		// Remove activation mode option
		delete_option( '_efpic_pro_activation_mode' );

		// Display admin notice
		add_filter( 'admin_notices', function() {
			?>
			<div class="notice notice-success is-dismissible">
				<p>✨ <?php /* translators: Admin notification text */ _e( 'All old Pro modules have been deactivated and deleted. You can now use efpic Pro!', 'efpic-pro' ); ?></p>
			</div>
			<?php
		});
	} 
}

add_action( 'admin_init', 'efpic_pro_deactivate_old_modules', 11 );

