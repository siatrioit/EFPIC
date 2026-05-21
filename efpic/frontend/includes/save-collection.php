<?php
/**
 * Save selection from the client as post meta data.
 *
 * This function will be called from the front end (client view).
 *
 * @since 0.4.0
 * @since 2.0.0 Moved frontend AJAX functionality here to separate from backend AJAX, previously in /backend/includes/efpic-ajax.php
 */
defined( 'ABSPATH' ) OR exit;


/**
 * Save/approve a collection for a client.
 *
 * Save the selection, triggered via AJAX. Maybe set status of a client to approved, if all requirements are met.
 *
 * @since 0.4.0
 * @since 2.3.0 The collection status will no longer change, ident is required for saving
 */
function efpic_send_selection() {
	// Validate save data
	efpic_validate_save();

	// Sanitize post id & ident
	$postid = sanitize_key( $_POST['postid'] );
	$ident = sanitize_key( $_POST['ident'] );

	// Check if the current user is allowed to make changes
	if ( ! in_array( efpic_get_status_from_ident( $postid, $ident ), [ 'open', 'sent', 'publish' ] ) ) {
		efpic_send_json( 'error', __( 'Error: You can not make any changes to this collection.', 'efpic' ) );
	}

	// Sanitize save data
	$save_data = efpic_sanitize_save();
	extract( $save_data );

	// Prepare array, that is saved as post meta
	$save = array(
		'selection' => $selection,
		'time' => time(),
		'markers' => $markers,
		'stars' => $stars,
	);

	// Save approval message
	if ( ! empty( $_POST['approval_fields']['efpic_approval_message']['value'] ) ) {
		$save['approval_fields']['efpic_approval_message']['value'] = trim( implode( "\n", array_map( 'sanitize_text_field', explode( "\n", stripslashes( $_POST['approval_fields']['efpic_approval_message']['value'] ) ) ) ) );
		$save['approval_fields']['efpic_approval_message']['label'] = __( 'Message', 'efpic' );
	}

	// Filter the $save array
	$save = apply_filters( 'efpic_save_selection', $save, $_POST );

	// Construct approval message, add all of the approval fields
	$approval_message = '';
	if ( ! empty( $save['approval_fields'] ) ) {
		foreach( $save['approval_fields'] as $key => $value ) {
			if ( !empty( $value['value'] ) ) {
				$approval_message .= '<strong>' . $value['label'] ."</strong>\n";
				if ( ! empty( $value['title'] ) ) {
					$approval_message .= $value['title'];
				}
				else {
					$approval_message .= $value['value'];
				}
				$approval_message .= "\n\n";
			}
		}
	}

	// Load existing save
	$previous_save = get_post_meta( $postid, '_efpic_collection_selection_' . $ident, true );

	// If there are no approval fields in the current save, we load them from the existing save and add it to the new save
	if ( empty( $save['approval_fields'] ) && ! empty( $previous_save['approval_fields'] ) ) {
		$save['approval_fields'] = $previous_save['approval_fields'];
	}

	// Save selection
	$response = update_post_meta( $postid, '_efpic_collection_selection_' . $ident, $save );

	// It worked
	if ( $response >= 1 ) {
		// Approve selection for client and send approval mail to photographer
		if ( is_array( $selection ) && count( $selection ) > 0 && isset( $_POST['intent'] ) && ( $_POST['intent'] == 'approve' || $_POST['intent'] == 'order' ) ) {

			// Run before processing the approval
			// Eg. hook in here to handle orders from the Pro plugin
			do_action( 'efpic_before_approval', $postid, $ident, $approval_message );

			// Update the status to approved for this client
			$collection_hashes = get_post_meta( $postid, '_efpic_collection_hashes', true );
			$collection_hashes[$ident]['status'] = 'approved';
			$collection_hashes[$ident]['time'] = time();
			update_post_meta( $postid, '_efpic_collection_hashes', $collection_hashes );

			// Update collection history
			efpic_update_collection_history( $postid, 'approved-by-client', efpic_combine_name_email( $collection_hashes[$ident]['name'], $collection_hashes[$ident]['email'] ), [ 'approval_message' => $approval_message ] );

			// Add approval message to email args:
			$args = [];
			$args['approval_message'] = $approval_message;

			// Send email notification to the photographer
			efpic_mail_approval( $postid, $ident, $args );

			do_action( 'efpic_after_approval', $postid, $ident );

			efpic_send_json( 'success', __( 'You successfully approved this collection.', 'efpic' ) );
		}
		// Return error if intent was to approve but no images was selected
		elseif ( is_array( $selection ) AND count( $selection ) <= 0 AND isset( $_POST['intent'] ) AND $_POST['intent'] == 'approve' ) {
			efpic_send_json( 'error', __( 'Please select at least one image.', 'efpic' ) );

		}
		// Regular "saved was a success" message
		else {
			do_action( 'efpic_after_saving_selection', $save, $previous_save, $postid, $ident );

			// If we want to regularly send data back to the front end, it needs to happen here!
			// TODO: Finalize filter name
			$data = apply_filters( 'efpic_send_data_to_frontend', [], $postid, $ident );

			efpic_send_json( 'success', __( 'Your selection was saved.', 'efpic' ), __( 'OK', 'efpic' ), $data );
		}

	}
	// It didn't work
	else {
		efpic_send_json( 'error', __( 'Error. Your selection could not be saved.', 'efpic' ) );
	}
}

add_action( 'wp_ajax_efpic_send_selection', 'efpic_send_selection' );
add_action( 'wp_ajax_nopriv_efpic_send_selection', 'efpic_send_selection' );


/**
 * Validate save data.
 *
 * @since 3.0.0
 */
function efpic_validate_save() {
	// Nonce check!
	if ( ! check_ajax_referer( 'efpic-ajax-security', 'security', false ) ) {
		efpic_send_json( 'error', __( '<strong>Error:</strong> Nonce check failed.<br />Refresh your browser window.', 'efpic' ) );
	}

	// Sanitize and validate post id
	$postid = sanitize_key( $_POST['postid'] );

	// Does this collection exist?
	if ( ! is_string( get_post_status( $postid ) ) ) {
		efpic_send_json( 'error', __( 'Error: Post id is not set.', 'efpic' ) );
	}

	// Post status needs to be 'publish' or 'sent'. In all other cases, a selection may not be saved
	if ( get_post_status( $postid ) == 'draft' ) {
		efpic_send_json( 'error', __( 'This collection is in draft mode for preview only. Publish it to allow image selection.', 'efpic' ) );
	}

	if ( ! in_array( get_post_status( $postid ), [ 'publish', 'sent' ] ) ) {
		efpic_send_json( 'error', __( 'Error: Collection is closed.', 'efpic' ) );
	}

	// Allow for some additional validation
	do_action( 'efpic_send_selection_validation', $_POST );

	// Check if ident parameter is set and if it exists in our collection
	if ( empty( $_POST['ident'] ) || ! efpic_ident_exists( sanitize_key( $_POST['ident'] ), $postid ) ) {
		efpic_send_json( 'error', __( 'Error: You are not authorized to change the selection.', 'efpic' ) );
	}

	// Sanitize and validate ident
	$ident = sanitize_key( $_POST['ident'] );

	// Check if the current user is allowed to make changes
	if ( ! in_array( efpic_get_status_from_ident( $postid, $ident ), [ 'open', 'sent', 'publish' ] ) ) {
		efpic_send_json( 'error', __( 'Error: You can not make any changes to this collection.', 'efpic' ) );
	}
}


/**
 * Sanitize save data.
 *
 * @since 3.0.0
 */
function efpic_sanitize_save() {
	// Sanitize selection
	if ( ! empty( $_POST['selection'] ) ) {
		$temp_selection = $_POST['selection'];
		$save_data['selection'] = array();
		foreach ( $temp_selection as $id ) {
			// Ids must be integer values, handing them over as strings
			$save_data['selection'][] = strval( intval( $id ) );
		}
	}
	else {
		$save_data['selection'] = false;
	}

	// Sanitize markers
	if ( ! empty( $_POST['markers'] ) ) {
		$save_data['markers'] = $_POST['markers'];

		function efpic_ajax_sanitize_comment( &$item, $key ) {
			if ( $key == 'comment' ) {
				$item = sanitize_text_field( $item );
			}
		}

		array_walk_recursive( $save_data['markers'], 'efpic_ajax_sanitize_comment' );
	}
	else {
		$save_data['markers'] = '';
	}


	// Sanitize stars
	if ( ! empty( $_POST['stars'] ) ) {
		$save_data['stars'] = $_POST['stars'];
	}
	else {
		$save_data['stars'] = '';
	}

	return $save_data;
}


/**
 * Send a JSON response.
 *
 * @since 1.6.5
 *
 * @param string $type Whether to send a 'success' or an 'error'
 * @param string $message (optional) Message to be sent back with the JSON data, default: 'An error occured'
 * @param string $button_text (optional) Text for the button we use in the error message, default: 'OK'
 * @param array $data Some data to send back to the frontend
 *
 */
function efpic_send_json( $type, $message = null, $button_text = null, $data = null ) {
	// Make sure $data is an array
	if ( empty( $data ) ) {
		$data = [];
	}
	$data = apply_filters( 'efpic_response_data', $data );

	// Prepare $message
	if ( ! isset ( $message ) ) {
		if ( $type == 'success' ) {
			$message = __( 'Success', 'efpic' );
		}
		else {
			$message = __( 'An error occurred', 'efpic' );
		}
	}

	// Set button text
	if ( ! isset ( $button_text ) ) {
		$button_text = __( 'OK', 'efpic' );
	}

	// Prepare return data
	$return = array(
		'update' => $data,
		'message' => $message,
		'button_text' => $button_text
	);

	if ( $type == 'success' ) {
		wp_send_json_success( $return );
	}
	else {
		wp_send_json_error( $return );
	}
}