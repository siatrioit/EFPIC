<?php
/**
 * Pro page & license activation
 *
 * @since 1.0.0
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Remove the Core add-ons subpage, add Pro page.
 * 
 * @since 1.0.0
 */
function efpic_pro_remove_core_add_ons_page() {
	remove_submenu_page( 'efpic', 'efpic-add-ons' );

	add_submenu_page( 'efpic', 'efpic Pro', 'efpic Pro', 'manage_options', EFPIC_PRO_LICENSE_PAGE, 'efpic_pro_load_subpage' );
}

add_action( 'admin_menu', 'efpic_pro_remove_core_add_ons_page', 11 );


/**
 * Register license settings.
 *
 * @since 1.0.0
 */
function efpic_pro_register_license() {
	register_setting( 'efpic_pro_license', 'efpic_pro_license_key' );
}

add_action( 'admin_init', 'efpic_pro_register_license' );


/**
 * Load the Pro subpage.
 * 
 * @since 1.0.0
 */
function efpic_pro_load_subpage() {
	$license = get_option( 'efpic_pro_license_key' );
	$license_status = efpic_pro_get_license_status();
	?>
	<div class="efpic-pro__head-wrapper">
		<h1 class="efpic-pro__head-line">efpic Pro</h1>
		<nav class="efpic-pro__head-nav">
			<a href="https://efpic.io/docs/"><?php /* translators: Link text */ _e( 'Documentation', 'efpic-pro' ); ?></a>
			<a href="https://efpic.io/support/"><?php /* translators: Link text */ _e( 'Support', 'efpic' ); ?></a>
		</nav>
	</div>
	<div class="wrap">
		<h2 style="display: none;"><!-- h2 headline is necessary for WordPress to properly position admin notices --></h2>
		<form class="efpic-pro__license-form" method="post" action="options.php">
			<?php settings_fields( 'efpic_pro_license' ); ?>
			<h3><label class="efpic-pro__license-label" for="efpic_pro_license_key">🪪 <?php _e( 'License', 'efpic-pro' ); ?></label></h3>
			<p class="efpic-pro__license-wrap">
				<input id="efpic_pro_license_key" name="efpic_pro_license_key" type="text" placeholder="<?php _e( 'Enter your license key', 'efpic-pro' ); ?>" class="regular-text" value="<?php esc_attr_e( $license ); ?>" <?php if ( ! empty( $license ) ) { echo ' disabled="disabled"'; } ?> autocomplete="off" />
				<?php
					if ( $license !== false ) {
						if ( $license_status !== false && $license_status == 'valid' ) { ?>
							<span class="efpic-pro__license-status efpic-pro__license-status--active"><?php _e( 'active', 'efpic-pro' ); ?></span>
					<?php }
						elseif ( $license_status == 'expired' ) { ?>
							<span class="efpic-pro__license-status efpic-pro__license-status--expired"><?php _e( 'expired', 'efpic-pro' ); ?></span>
					<?php }
					}
				?>
			</p>
			<p>
			<?php wp_nonce_field( 'efpic_pro_nonce', 'efpic_pro_nonce' ); ?>
			<?php 
			if ( $license_status !== false && $license_status == 'valid' ) { ?>
				<input type="submit" class="button-secondary" name="efpic_pro_license_deactivate" value="<?php _e( 'Deactivate License', 'efpic-pro' ); ?>" />
			
			<?php } else { ?>
				<input type="submit" class="button-primary" name="efpic_pro_license_activate" value="<?php _e( 'Activate License', 'efpic-pro' ); ?>" />
			<?php } ?>
			</p>
			<p><strong><?php _e( 'Need help?', 'efpic-pro' ); ?></strong> <?php echo sprintf( __( 'Read about %sefpic Pro licenses and activation%s.', 'efpic-pro' ), '<a href="https://efpic.io/docs/pro/#license-activation">', '</a>' ); ?></p>
			
		</form>
	<?php
}


/**
 * Retrieve license info from efpic.io.
 *
 * @since 1.0.0
 *
 * @param string $license The license
 * @param string $action The EDD action to perform, eg. activate or deactivate
 * @return array|WP_Error The response or WP_Error on failure.
 */
function efpic_pro_check_license( $license, $action ) {
	$api_params = [
		'edd_action' => $action,
		'item_name' => urlencode( EFPIC_PRO_NAME ),
		'license' => $license,
		'url' => home_url()
	];

	$response = wp_remote_post( 'https://efpic.io', [
		'timeout' => 15,
		'sslverify' => true,
		'body' => $api_params
	] );

	return $response;
}


/**
 * Load license data.
 *
 * @since 1.0.0
 *
 * @return object The license data, eg. if it is valid, expiry date, etc.
 */
function efpic_pro_get_license_data() {
	// Get infos from transient
	$license_data = get_transient( 'efpic_pro_license_status' );

	// If trasient doesn't exist, send request to efpic.io
	if ( ! $license_data ) {
		$license = get_option( 'efpic_pro_license_key' );
		$response = efpic_pro_check_license( $license, EFPIC_PRO_NAME, 'check_license' );
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	}

	return $license_data;
}


/**
 * Get license status.
 *
 * @since 1.0.0
 * 
 * @return string The license status
 */
function efpic_pro_get_license_status() {
	$license_status = '';
	$license_data = efpic_pro_get_license_data();
	if ( ! empty( $license_data->license ) ) {
		$license_status = $license_data->license;
	}
	return $license_status;
}


/**
 * Activate or deactivate license.
 *
 * Validate license form data.
 *
 * @since 1.0.0
 */
function efpic_pro_activate_license() {
	// Activate button was clicked
	if ( isset( $_POST['efpic_pro_license_activate'] ) ) {

	 	if( ! check_admin_referer( 'efpic_pro_nonce', 'efpic_pro_nonce' ) ) {
			return;
		}

		$license = trim( $_POST['efpic_pro_license_key'] );
		$response = efpic_pro_check_license( $license, 'activate_license' );

		// Make sure the response came back okay
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'efpic-pro' );
			}

		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( $license_data->success === false ) {

				switch( $license_data->error ) {
					case 'expired' :
						$message = sprintf( __( 'Your license expired on %s.', 'efpic-pro' ), date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) ) );
						break;
					case 'disabled' :
					case 'revoked' :
						$message = __( 'Your license has been disabled.', 'efpic-pro' );
						break;
					case 'missing' :
						$message = __( 'The license you entered is invalid.', 'efpic-pro' );
						break;
					case 'invalid' :
					case 'site_inactive' :
						$message = __( 'Your license is not active for this URL.', 'efpic-pro' );
						break;
					case 'item_name_mismatch' :
						$message = sprintf( __( 'This appears to be an invalid license for %s.', 'efpic-pro' ), EFPIC_PRO_NAME );
						break;
					case 'no_activations_left':
						$message = __( 'Your license has reached its activation limit.', 'efpic-pro' );
						break;
					default :
						$message = __( 'An error occurred, please try again.', 'efpic-pro' );
						break;
				}
			}
		}

		$activation = 'error';

		// If there is no message, activation was successful
		if ( empty( $message ) ) {
			$activation = 'success';
			$message = __( '🎉 Your license was activated.', 'efpic-pro' );
			update_option( 'efpic_pro_license_key', $license, false );
			set_transient( 'efpic_pro_license_status', $license_data, 'DAY_IN_SECONDS' );
		}
		else {
			delete_option( 'efpic_pro_license_key', $license );
			set_transient( 'efpic_pro_license_status', $license_data, 'DAY_IN_SECONDS' );
		}

		// Redirect and display message
		$base_url = admin_url( 'admin.php?page=' . EFPIC_PRO_LICENSE_PAGE );
		$redirect = add_query_arg( [ 'efpic_pro_activation' => $activation, 'message' => urlencode( $message ) ], $base_url );
		wp_redirect( $redirect );
		exit();
	}

	// Deactivation button was clicked
	if ( isset( $_POST['efpic_pro_license_deactivate'] ) ) {
		$license = trim( get_option( 'efpic_pro_license_key' ) );

		$response = efpic_pro_check_license( $license, 'deactivate_license' );

		// Make sure the response came back okay
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {

			$deactivation = 'error';

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'efpic-pro' );
			}

		} else {
			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if ( $license_data->license == 'deactivated' ) {
				$deactivation = 'success';
				delete_option( 'efpic_pro_license_key' );
				delete_transient( 'efpic_pro_license_status' );
				/* translators: Admin notice */
				$message = __( 'You successfully deactivated your license.', 'efpic-pro' );
			}
			else {
				// Deactivation didn't work, delete the license anyway…
				$deactivation = 'error';
				delete_option( 'efpic_pro_license_key' );
				delete_transient( 'efpic_pro_license_status' );
				/* translators: Admin notics; %s = opening and closing link tags */
				$message = sprintf( __( 'Your License could not be deactivated. To manage your licenses log into your %sefpic account%s.', 'efpic-pro' ), '<a href="https://efpic.io/account/#tab-licenses">', '</a>' );
			}
		}

		// Redirect and display success message
		$base_url = admin_url( 'admin.php?page=' . EFPIC_PRO_LICENSE_PAGE );
		$redirect = add_query_arg( [ 'efpic_pro_deactivation' => $deactivation, 'message' => urlencode( $message ) ], $base_url );
		wp_redirect( $redirect );
		exit();
	}
}

add_action( 'admin_init', 'efpic_pro_activate_license', 11 );


/**
 * Display licensing notifications
 * 
 * @since 1.0.0
 */
function efpic_pro_admin_notices() {
	if ( isset( $_GET['efpic_pro_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['efpic_pro_activation'] ) {
			case 'error':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="notice notice-error is-dismissable">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'success':
			default:
			$message = urldecode( $_GET['message'] );
				?>
				<div class="notice notice-success is-dismissable">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;
		}
	}
	elseif ( isset( $_GET['efpic_pro_deactivation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['efpic_pro_deactivation'] ) {
			case 'error':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="notice notice-error is-dismissable">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'success':
			default:
			$message = urldecode( $_GET['message'] );
				?>
				<div class="notice notice-success is-dismissable">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;
		}
	}
}

add_action( 'admin_notices', 'efpic_pro_admin_notices' );