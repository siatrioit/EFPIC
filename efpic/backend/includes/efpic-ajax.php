<?php
/**
 * Save Pro box and telemetry nag state.
 *
 * @since 0.4.0
 * @since 2.0.0 Moved frontend AJAX stuff to separate file: /frontend/includes/save-collection.php
 */
defined( 'ABSPATH' ) OR exit;


/**
 * Save visibility state of the efpic pro meta box
 *
 * @since 1.3.1
 */
function efpic_save_pro_box_state() {
	if ( ! check_ajax_referer( 'efpic_ajax', 'security', false ) ) {
		efpic_send_json( 'error', __( '<strong>Error:</strong> Nonce check failed.', 'efpic' ) );
	}

	if ( isset( $_REQUEST['efpic_hide_pro_box'] ) AND $_REQUEST['efpic_hide_pro_box'] ==
'true' ) {
		set_transient( 'efpic_pro_box_hidden_' . get_current_user_id(), true, YEAR_IN_SECONDS );
	}
	else {
		delete_transient( 'efpic_pro_box_hidden_' . get_current_user_id() );
	}

	wp_send_json_success( $_REQUEST );

	exit;
}
add_action( 'wp_ajax_efpic_save_pro_box_state', 'efpic_save_pro_box_state' );


/**
 * Hide the telemetry nag notice for a while.
 *
 * @since 1.10.0
 */
function efpic_save_telemetry_nag_state() {
	if ( ! check_ajax_referer( 'efpic_ajax', 'security', false ) ) {
		efpic_send_json( 'error', __( '<strong>Error:</strong> Nonce check failed.', 'efpic' ) );
	}

	// Increase the telemetry nag
	$dismissed = get_option( 'efpic_telemetry_nag', 0 ) + 1;
	update_option( 'efpic_telemetry_nag', $dismissed, false );

	// Hide nag for one week
	set_transient( 'efpic_telemetry_nag_' . get_current_user_id(), true, WEEK_IN_SECONDS );
	wp_send_json_success();
	exit;
}

add_action( 'wp_ajax_efpic_save_telemetry_nag_state', 'efpic_save_telemetry_nag_state' );