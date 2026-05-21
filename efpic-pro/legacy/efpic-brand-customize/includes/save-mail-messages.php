<?php
/**
 * Brand & Customize AJAX handling
 *
 * @since brand-customize (1.4.0)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Save custom email messages.
 *
 * @since brand-customize (1.4.0)
 */
function efpic_save_custom_email_messages() {
	if ( ! check_ajax_referer( 'efpic_ajax', 'security', false ) ) {
		$return = array(
			'message' => __( '<strong>Error:</strong> Nonce check failed.', 'efpic' )
		);
		wp_send_json_error( $return );
		exit;
	}

	$messages = array();

	if ( isset( $_REQUEST['efpic_email_templates'] ) ) {
		// Sanitize
		foreach( $_REQUEST['efpic_email_templates'] as $message ) {
			$messages[] = array(
				'message_name' => stripslashes( implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $message['message_name'] ) ) ) ),
				'message_body' => stripslashes ( implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $message['message_body'] ) ) ) ),
				'message_default' => boolval( $message['message_default'] ),
			);
		}
		
		update_option( 'efpic_email_templates', $messages, false );
	}
	else {
		// Save empty settings
		update_option( 'efpic_email_templates', array(), false );
	}

	wp_send_json_success( $messages );
	exit;
}

add_action( 'wp_ajax_efpic_save_custom_email_messages', 'efpic_save_custom_email_messages' );