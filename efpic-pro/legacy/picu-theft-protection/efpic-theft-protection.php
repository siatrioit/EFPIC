<?php
/**
 * Theft Protection
 *
 * Add watermarks, disable right-clicks and more.
 *
 * @since theft-protection (0.0.1)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Initialize Theft Protection.
 *
 * @since theft-protection (0.0.1)
 */
require_once EFPIC_PRO_PATH . 'legacy/efpic-theft-protection/includes/disable-right-click.php';
require_once EFPIC_PRO_PATH . 'legacy/efpic-theft-protection/includes/watermarking.php';


/**
 * Display notice, if watermark is enabled.
 *
 * @since theft-protection (0.2.0)
 * 
 * @see efpic/backend/includes/efpic-edit-collection.php
 * 
 * @param string $efpic_before_upload HTML content
 * @return string The filtered HTML content
 */
function efpic_theft_protection_watermark_notice( $efpic_before_upload ) {
	// Get theft protection options
	$watermark = get_option( 'efpic_watermark' );

	// Check if watermark is set
	if ( ! empty( $watermark['watermark'] ) ) {

		$watermark_by_default = '';

		if ( isset( $watermark['watermark_by_default'] ) AND $watermark['watermark_by_default'] == 'on' ) {
			$watermark_by_default = ' checked="checked"';
		}

		// Check collection setting
		$apply_watermark = get_post_meta( get_the_ID(), '_efpic_apply_watermark', true );
		if ( isset( $apply_watermark ) AND $apply_watermark == 'on' ) {
			$watermark_by_default = ' checked="checked"';
		}
		elseif ( $apply_watermark == 'off' ) {
			$watermark_by_default = '';
		}

		ob_start();
?>
	<div class="efpic-theft-protection-watermark-toggle">
		<input type="checkbox" id="efpic-apply-watermark" name="efpic_apply_watermark"<?php echo $watermark_by_default; ?> />
		<label for="efpic-apply-watermark"><?php _e( 'Apply watermark to new images', 'efpic-pro' ); ?></label>
		<span class="efpic-watermark-saving-indicator">
			<span class="efpic-watermark-saving"><?php _e( 'Saving', 'efpic-pro' ); ?></span>
			<span class="efpic-watermark-saved"><?php _e( 'Saved', 'efpic-pro' ); ?></span>
		</span>
	</div>

<?php
		return ob_get_clean() . $efpic_before_upload;
	}

	return $efpic_before_upload;
}

add_filter( 'efpic_before_upload', 'efpic_theft_protection_watermark_notice' );


/**
 * Save custom watermark state for a collection.
 *
 * @since theft-protection (0.5.0)
 */
function efpic_save_watermark_state() {

	if ( ! check_ajax_referer( 'efpic_ajax', 'security', false ) ) {
		$return = array(
			'message' => __( '<strong>Error:</strong> Nonce check failed.', 'efpic' )
		);
		wp_send_json_error( $return );
		exit;
	}

	// Save
	if ( isset( $_POST['efpic_apply_watermark'] ) AND $_POST['efpic_apply_watermark'] == 'on' ) {
		update_post_meta( $_POST['post_id'], '_efpic_apply_watermark', 'on' );
	}
	else {
		update_post_meta( $_POST['post_id'], '_efpic_apply_watermark', 'off' );
	}

	wp_send_json_success( array(
		'message' => __( 'Setting saved.', 'efpic' )
	) );
	exit;
}

add_action( 'wp_ajax_efpic_save_watermark_state', 'efpic_save_watermark_state' );