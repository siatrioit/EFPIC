<?php
/**
 * Handle client registration
 * 
 * @since 1.4.0
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Register new client
 *
 * @since 1.4.0
 */
function efpic_pro_register_client() {
	// Nonce check!
	if ( ! check_ajax_referer( 'efpic-ajax-security', 'security', false ) ) {
		efpic_send_json( 'error', __( '<strong>Error:</strong> Nonce check failed.<br />Refresh your browser window.', 'efpic-pro' ) );
		exit;
	}

	// Sanitize and validate post id
	$post_id = sanitize_key( $_POST['postid'] );
	// Check if the collection exists
	if ( ! get_post_status( $post_id ) ) {
		efpic_send_json( 'error', __( 'Error: Post id is not set.', 'efpic-pro' ) );
		exit;
	}

	// Check post status / collection is already approved or has expired
	$post_status = get_post_status( $post_id );

	// Post status needs to be 'publish' or 'sent'
	if ( $post_status == 'approved' ) {
		efpic_send_json( 'error', __( 'Error: Collection is already approved.', 'efpic-pro' ) );
		exit;
	}
	elseif ( $post_status == 'expired' ) {
		efpic_send_json( 'error', __( 'Error: Collection has expired.', 'efpic-pro' ) );
		exit;
	}

	// Handle email
	$email = '';
	if ( ! empty( $_POST['registration_fields']['email']['value'] ) ) {
		$email = $_POST['registration_fields']['email']['value'];
		if ( ! is_email( $email ) ) {
			efpic_send_json( 'error', __( 'Please enter a valid email address.', 'efpic-pro' ) );
			exit;
		}
	}

	// Handle name
	$name = '';
	if ( ! empty( $_POST['registration_fields']['name']['value'] ) ) {
		$name = sanitize_text_field( $_POST['registration_fields']['name']['value'] );
	}

	// Try to add the new client
	$new_client = efpic_pro_add_client( $post_id, $email, $name );

	// It worked
	if ( ! empty( $new_client['ident'] ) && ! empty( $new_client['msg'] ) ) {
		wp_send_json_success( [ 'ident' => $new_client['ident'], 'verification' => $new_client['verification'], 'message' => $new_client['msg'] ] );
		exit;
	}

	// There is an error
	// TODO: Maybe send email with the ident URL, if the email address is already registered. Display a notice that says so.
	if ( ! empty( $new_client['error'] ) && ! empty( $new_client['msg'] ) ) {
		efpic_send_json( 'error', $new_client['msg'] );
		exit;
	}

	// Unspecific error message
	efpic_send_json( 'error', __( '<strong>Registration failed.</strong> Please try again later.', 'efpic-pro' ) );
	exit;
}

add_action( 'wp_ajax_efpic_register', 'efpic_pro_register_client' );
add_action( 'wp_ajax_nopriv_efpic_register', 'efpic_pro_register_client' );


/**
 * Add a client to a collection
 *
 * @since 2.3.0
 *
 * @param int $collection_id The collection post ID
 * @param string $email The client's email address
 * @param string $email The client's name
 * @return bool Whether it worked or not
 */
function efpic_pro_add_client( $collection_id, $email = '', $name = '' ) {
	// Check if there are already any hashes
	$collection_hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );
	if ( ! is_array( $collection_hashes ) ) {
		$collection_hashes = [];
	}

	// Check if an email address is required
	$email_required = get_option( 'efpic_registration_email_required' );
	if ( $email_required && empty( $email ) ) {
		return $new_client = [
			'error' => 'email-empty',
			'msg' => __( 'You have to provide an email address to continue.', 'efpic-pro' )
		];
	}

	// Make sure at least name or email are set
	if ( empty( $email ) && empty( $name ) ) {
		return $new_client = [
			'error' => 'name-email-empty',
			'msg' => __( 'You need to enter either a name or an email address before you can make a selection.', 'efpic-pro' )
		];
	}

	// Check if the new email already exists
	$index = array_search( $email, array_column( $collection_hashes, 'email' ) );
	if ( $index !== false && ! empty( $email ) ) {
		// E-Mail is already part of this collection

		// Send the email again
		$ident = efpic_get_ident_from_email( $collection_id, $email );
		efpic_pro_mail_client_confirmation( $collection_id, $email, $ident );

		$new_client = [
			'ident' => $ident,
			'verification' => 1,
			'msg' => __( 'Email with collection link sent.', 'efpic-pro' )
		];

		return $new_client;
	}

	// Check if the name already exists
	$index = array_search( $name, array_column( $collection_hashes, 'name' ) );
	if ( empty( $email ) && $index !== false && ! empty( $name ) ) {
		// Name is already part of this collection
		return $new_client = [
			'error' => 'name-already-exists',
			'msg' => sprintf( __( '<strong>The name "%s" is already in use for this collection.</strong><br />Either add more details (eg. your surname) or enter your email address to continue.', 'efpic-pro' ), $name ),
		];
	}

	// Create new client
	$ident = efpic_add_client_to_hashes( $collection_id, $name, $email );

	if ( $ident ) {
		efpic_update_collection_history( $collection_id, 'new-client-registered', efpic_combine_name_email( $name, $email ) );

		$new_client = [
			'ident' => $ident,
			'verification' => 0, // Verification not required
			'msg' => __( 'Registration successful.', 'efpic-pro' )
		];

		if ( ! empty( $email ) ) {
			// Send verification email
			efpic_pro_mail_client_confirmation( $collection_id, $email, $ident );

			$new_client['verification'] = 1;
		}

		return $new_client;
	}

	return [
		'error' => 'error',
		'msg' => __( 'An error occured', 'efpic-pro' ),
	];
}


/**
 * Email collection URL to new recipient
 *
 * @since 2.3.0
 *
 * @param int $collection_id The collection post id
 * @param string $to_address The recipient email address
 * @param string $ident The post object
 * @param bool $preview Wether the function should used for preview
 */
function efpic_pro_mail_client_confirmation( $collection_id, $to_address, $ident, $preview = false ) {
	$post = get_post( $collection_id );
	$mail = new Efpic_Emails( $collection_id );

	$args = [];

	// Set context
	$args['mail_context'] = 'new_client_confirmation';

	// Add text part
	$args['mail_parts']['new_client_confirmation_text'] =
	[
		'type' => 'text',
		'text' => $mail->text_to_html( __( "Thanks!\n\nPlease follow the link below to view the images and make your selection.", 'efpic-pro' ) )
	];

	// Maybe include expiration part
	$args = efpic_maybe_include_expiration_info( $args, $post );

	// Maybe include password part
	$args = efpic_maybe_include_password( $args, $post );

	// Setup error tracking
	$send_error = false;

	$args['to_address'] = $to_address;
	$args['hash'] = $ident; // Might come in handy inside the email class…

	// Add collection link
	$args['mail_parts']['collection_link'] = [
		'type' => 'button',
		'text' => __( 'Start selecting images', 'efpic-pro' ),
		'url' => isset( $ident ) ? esc_url( add_query_arg( 'ident', $ident, get_draft_permalink( $collection_id ) ) ) : esc_url( get_draft_permalink( $collection_id ) )
	];

	$args['attachments'] = [];
	$args['cc_email'] = null;

	$mail->setArgs( $args );

	if ( $preview === true ) {
		return $mail->build( $args );
	}

	if ( $mail->send() !== true ) {
		$send_error = true;
	}

	// If an error occured, save that in the collection history
	if ( $send_error === true ) {
		efpic_update_collection_history( $collection_id, 'error', sprintf( __( 'Error sending confirmation email to %s', 'efpic-pro' ), $to_address ) );
	}

	return true;
}