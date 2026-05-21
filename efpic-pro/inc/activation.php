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


/**
 * Mybe use existing Pro licenses.
 * 
 * @since 1.0.0
 */
function efpic_pro_convert_old_licenses() {
	// If there is aleary a Pro license, skip the rest
	$pro_license_key = get_option( 'efpic_pro_license_key' );

	if ( ! empty( $pro_license_key ) ) {
		return;
	}

	// Check old licenses
	$licenses = get_option( 'efpic_addon_licenses' );

	// Prepare variables
	$license_evaluation = [];
	$checked_licenses = [];

	if ( ! empty( $licenses ) AND is_array( $licenses ) ) {

		foreach( $licenses as $license_key ) {
			// Make sure we do not check the same license muliple times
			if ( ! in_array( $license_key, $checked_licenses ) ) {
				$checked_licenses[] = $license_key;

				// Check the licenses
				$response = efpic_check_license( $license_key, EFPIC_PRO_NAME, 'check_license' );
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// Add it to the pile, if it's valid
				if ( $license_data->license == 'valid' ) {
					$license_evaluation[$license_key] = [
						'expires' => strtotime( $license_data->expires, current_time( 'timestamp' ) ),
						'license_data' => $license_data
					];
				}
			}
		}
	}

	if ( count( $license_evaluation ) > 0 ) {
		// Sort by expiry date, then use the license that is valid the longest
		$key = array_column( $license_evaluation, 'expires' );
		array_multisort( $key, SORT_ASC, $license_evaluation );
		update_option( 'efpic_pro_license_key', array_key_last( $license_evaluation ), false );

		// Save license status
		set_transient( 'efpic_pro_license_status', $license_data, DAY_IN_SECONDS );

		// Display admin nofice
		add_filter( 'admin_notices', function() {
			?>
			<div class="notice notice-success is-dismissible">
				<?php /* translators: Admin notice; %s = opening and closing link tags. */ ?>
				<p>🪪 <?php echo sprintf( __( 'Your existing %sefpic Pro license%s has automatically been activated.', 'efpic-pro' ), '<a href="' . admin_url( 'admin.php?page=efpic-pro' ). '">', '</a>' ); ?></p>
			</div>
			<?php
		});

	}
}

add_action( 'admin_init', 'efpic_pro_convert_old_licenses', 12 );