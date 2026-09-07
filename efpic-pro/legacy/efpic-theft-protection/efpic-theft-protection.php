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

		$watermark_on = false;

		if ( isset( $watermark['watermark_by_default'] ) AND $watermark['watermark_by_default'] == 'on' ) {
			$watermark_on = true;
		}

		// Check collection setting
		$apply_watermark = get_post_meta( get_the_ID(), '_efpic_apply_watermark', true );
		if ( isset( $apply_watermark ) AND $apply_watermark == 'on' ) {
			$watermark_on = true;
		}
		elseif ( $apply_watermark == 'off' ) {
			$watermark_on = false;
		}

		ob_start();
?>
	<div class="efpic-theft-protection-watermark-toggle efpic-option-toggle-row">
		<?php
		if ( function_exists( 'efpic_feature_on_off_toggle' ) ) {
			echo efpic_feature_on_off_toggle( 'efpic_apply_watermark', $watermark_on ? 'on' : 'off', 'efpic-apply-watermark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
		<span class="efpic-option-toggle-row__label"><?php esc_html_e( 'Apply watermark to new images', 'efpic-pro' ); ?></span>
		<span class="efpic-watermark-saving-indicator">
			<span class="efpic-watermark-saving"><?php esc_html_e( 'Saving', 'efpic-pro' ); ?></span>
			<span class="efpic-watermark-saved"><?php esc_html_e( 'Saved', 'efpic-pro' ); ?></span>
		</span>
	</div>
	<script>
	(function ($) {
		$(function () {
			$(document).on('change', '#efpic-apply-watermark', function () {
				$('.efpic-watermark-saving-indicator').removeClass('is-saved').addClass('is-saving');
				var state = $(this).val() === 'on' ? 'on' : 'off';
				$.post(efpic_admin.ajaxurl, {
					action: 'efpic_save_watermark_state',
					security: efpic_admin.ajax_nonce,
					post_id: $('#post_ID').val(),
					efpic_apply_watermark: state
				}).done(function () {
					$('.efpic-watermark-saving-indicator').removeClass('is-saving').addClass('is-saved');
				});
			});
		});
	})(jQuery);
	</script>

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