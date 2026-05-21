<?php
/**
 * Selection Options
 *
 * Enables selection goals. Choose exact, minimum, maximum or a range of images for the client to approve.
 *
 * @since selection-options (0.0.1)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Collection option output.
 *
 * @since selection-option (0.0.1)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 * @global object $post The post object
 *
 * @param array $option_output Collection options, the key is used as div id
 * @return array The filtered options
 */
function efpic_selection_options_add_collection_option( $options_output ) {
	global $post;

	$options = get_post_meta( $post->ID, '_efpic_collection_selection_options', true );

	if ( ! isset( $options['restriction'] ) ) {
		$options = array(
			'selection_option' => false,
			'restriction' => false,
			'from' => false,
			'to' => false,
		);
	}

	// Disable option when collection has been sent
	$disabled = ( 'sent' == $post->post_status ) ? ' disabled' : '';

	// Generate option output
	ob_start();

	echo '<p><input type="checkbox" class="js-collapse-control" id="efpic_selection_options" name="efpic_selection_options" ' . checked( true, $options['selection_option'], false ) . ' ' . $disabled . ' autocomplete="off" /> <label for="efpic_selection_options">' . __( 'Set Selection Goal', 'efpic-pro' ) . '&hellip;</label></p>';
	echo '<div class="js-collapsible';

	if ( true != $options['selection_option'] ) {
		echo ' is-collapsed';
	}

	echo '" id="efpic-selection-options-options">';

	echo '<p><label for="efpic-selection-option">' . __( 'The client needs to select', 'efpic-pro' ) . '</label>
			<select name="efpic-selection-option" id="efpic-selection-option"' . $disabled . '>
				<option value="exactly" ' . selected( 'exactly', $options['restriction'], false ) . '>' . __( 'exactly', 'efpic-pro' ) . '</option>
				<option value="at least" ' . selected( 'at least', $options['restriction'], false ) . '>' . __( 'at least', 'efpic-pro' ) . '</option>
				<option value="a maximum of" ' . selected( 'a maximum of', $options['restriction'], false ) . '>' . __( 'a maximum of', 'efpic-pro' ) . '</option>
				<option value="in the range of" ' . selected( 'in the range of', $options['restriction'], false ) . '>' . __( 'in the range of', 'efpic-pro' ) . '</option>
			</select>
			<input type="number" name="efpic-selection-option-image-from" id="efpic-selection-option-image-from"';
			if ( isset( $options['from'] ) AND ! empty( $options['from'] ) ) { echo ' value="' . $options['from'] . '"'; }
			echo $disabled . ' />';
		echo ' <span class="efpic-range"';
		if ( 'in the range of' != $options['restriction'] ) { echo ' style="display: none;"'; }
		echo '><span class="efpic-optional-range">' . __( 'to', 'efpic-pro' ) . '</span> <input type="number" name="efpic-selection-option-image-to" id="efpic-selection-option-image-to"';
			if ( isset( $options['to'] ) AND ! empty( $options['to'] ) ) { echo ' value="' . $options['to'] . '"'; }
		echo $disabled . ' /></span> <span>' . __( 'image(s)', 'efpic-pro' ) . '</span></p>';

	echo '</div>';

	$options_output['efpic-selection-options'] = ob_get_clean();

	return $options_output;
}

add_filter( 'efpic_collection_options', 'efpic_selection_options_add_collection_option', 100 );


/**
 * Sanitize & save options.
 *
 * @since selection-options (0.0.1)
 *
 * @param int $post_id The collection post ID
 */
function efpic_selection_options_save_collection( $post_id ) {
	// Check if nonce is set
	if ( ! isset( $_POST['efpic_gallery_ids_nonce'] ) )
		return $post_id;

	// Verify that the nonce is valid
	if ( ! wp_verify_nonce( $_POST['efpic_gallery_ids_nonce'], 'efpic_gallery_ids' ) )
		return $post_id;

	// Check user permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	// Validate selection options
	$allowed_options = array( 'exactly', 'at least', 'a maximum of', 'in the range of' );
	if ( ! in_array( $_POST['efpic-selection-option'], $allowed_options ) ) {
		return $post_id;
	}

	if ( isset( $_REQUEST['efpic_gallery_ids'] ) ) {
		$num = count( explode( ',', $_REQUEST['efpic_gallery_ids'] ) );
	} else {
		$num = 0;
	}

	// Check options, save as meta data accordingly
	if ( isset( $_POST['efpic_selection_options'] ) AND 'on' == $_POST['efpic_selection_options'] ) {

		// Check from value
		if ( isset( $_POST['efpic-selection-option-image-from'] ) AND ! empty( $_POST['efpic-selection-option-image-from'] ) AND 0 < intval( $_POST['efpic-selection-option-image-from'] ) ) {
			$from = intval( $_POST['efpic-selection-option-image-from'] );

			if ( $from > $num ) {
				efpic_add_notification( 'efpic_selection_option_from_number_missing', 'notice notice-error is-dismissible', sprintf( __( 'The selection goal can\'t be higher than the number of images in your collection (which is %s).', 'efpic-pro' ), $num ) );
			}
		} else {
			efpic_add_notification( 'efpic_selection_option_from_number_missing', 'notice notice-error is-dismissible', __( 'When specifying the selection goal, please enter a number, larger than 0.', 'efpic-pro' ) );
			return $post_id;
		}

		// Check to value
		if ( 'in the range of' == $_POST['efpic-selection-option'] ) {
			if ( isset( $_POST['efpic-selection-option-image-to'] ) AND ! empty( $_POST['efpic-selection-option-image-to'] ) AND 0 < intval( $_POST['efpic-selection-option-image-to'] ) ) {
				if ( intval( $_POST['efpic-selection-option-image-from'] ) > intval( $_POST['efpic-selection-option-image-to'] ) ) {
					efpic_add_notification( 'efpic_selection_option_to_number_missing', 'notice notice-error is-dismissible', __( 'When selecting a range, the maximum needs to be higher than the minimum number of images.', 'efpic-pro' ) );
				} else {
					$to = intval( $_POST['efpic-selection-option-image-to'] );

					if ( $to > $num ) {
						efpic_add_notification( 'efpic_selection_option_from_number_missing', 'notice notice-error is-dismissible', sprintf( __( 'The range maximum can\'t be higher than the number of images in your collection (which is %s).', 'efpic-pro' ), $num ) );
					}
				}
			} else {
				efpic_add_notification( 'efpic_selection_option_to_number_missing', 'notice notice-error is-dismissible', __( 'When selecting a range, please specify the maximum number of images.', 'efpic-pro' ) );
			}
		} else {
			$to = '';
		}

		// Build options array
		$temp = array(
			'selection_option' => true,
			'restriction' => $_POST['efpic-selection-option'],
			'from' => $from,
			'to' => $to
		);

		update_post_meta( $post_id, '_efpic_collection_selection_options', $temp );
	} else {
		delete_post_meta( $post_id, '_efpic_collection_selection_options' );
	}
}

add_action( 'save_post_efpic_collection', 'efpic_selection_options_save_collection', 9 );


/**
 * Pass selection options on to appstate.
 *
 * @since selection-options (1.1.0)
 *
 * @see efpic/frontend/includes/efpic-template-functions.php
 *
 * @param array $state The app state
 * @return array The filtered app state
 */
function efpic_selection_options_appstate( $state ) {
	$post = get_post();

	$options = get_post_meta( $post->ID, '_efpic_collection_selection_options', true );

	if ( isset( $options ) AND is_array( $options ) ) {
		$state['selection_restriction'] = $options;

		// Add info message about selection options
		$selection_info = efpic_get_selection_options_info_message( $options['restriction'], $options['from'], $options['to'] );

		$state['selection_restriction']['selection_info'] = $selection_info;
	}

	return $state;
}

add_action( 'efpic_app_state', 'efpic_selection_options_appstate' );


/**
 * Return selection options message.
 * 
 * Explains how many images need to be selected. Used in the front end
 * and in emails to the client.
 *
 * @since selection-options (0.0.3)
 *
 * @param string $restriction Which restriction is in place
 * @param int $from Minimum/general amount of images to be selected
 * @param int $to Optional. Maximum amount of images to be selected
 * @return string The selection options message
 */
function efpic_get_selection_options_info_message( $restriction, $from, $to = NULL ) {
	if ( 'exactly' == $restriction ) {
		$selection_info = sprintf( _n( 'You need to select exactly one image.', 'You need to select exactly %s images.', $from, 'efpic-pro' ), $from );
	}
	elseif ( 'at least' == $restriction ) {
		$selection_info = sprintf( _n( 'You need to select at least one image.', 'You need to select at least %s images.', $from, 'efpic-pro' ), $from );
	}
	elseif ( 'a maximum of' == $restriction ) {
		$selection_info = sprintf( _n( 'You are allowed to select exactly one image.', 'You are allowed to select a maximum of %s images.', $from, 'efpic-pro' ), $from );
	}
	elseif ( 'in the range of' == $restriction ) {
		$selection_info = sprintf( __( 'You need to select between %s and %s images.', 'efpic-pro' ), $from,  $to );
	}

	$selection_info = apply_filters( 'efpic_selection_options_info_message', $selection_info, $restriction, $from, $to );

	return $selection_info;
}


/**
 * Replace status-bar template
 *
 * @since selection-options (0.0.2)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param array $templates The front end backbone templates
 * @return array The filtered templates
 */
function efpic_selection_options_status_bar_template( $templates ) {
	$templates['status-bar'] = EFPIC_PRO_PATH . 'legacy/efpic-selection-options/templates/status-bar.php';
	$templates['collection-info'] = EFPIC_PRO_PATH . 'legacy/efpic-selection-options/templates/collection-info.php';

	return $templates;
}

add_action( 'efpic_load_backbone_templates', 'efpic_selection_options_status_bar_template' );


/**
 * Add selection options info to efpic emails.
 *
 * @since selection-options (1.3.0)
 *
 * @see efpic/backend/includes/emails/class-efpic-emails.php
 *
 * @param array $mail_parts The mail parts that make up the email
 * @param string $mail_context Defines which email this is
 * @param int $post_id The collection post ID
 * @return array The mail parts containing the selection options info
 */
function efpic_selection_options_mail_part( $mail_parts, $mail_context, $post_id ) {
	if ( $mail_context == 'client_collection_new' ) {

		$options = get_post_meta( $post_id, '_efpic_collection_selection_options', true );

		$selection_info = '';

		if ( isset( $options['selection_option'] ) AND true == $options['selection_option'] AND get_post_status( $post_id ) != 'delivery-draft' ) {
			$selection_info = efpic_get_selection_options_info_message( $options['restriction'], $options['from'], $options['to'] );
		}

		$selection_info = apply_filters( 'efpic_selection_options_selection_info', $selection_info, $post_id );

		$temp['selection_options'] = [
			'type' => 'text',
			'text' => $selection_info,
		];

		// Insert selection option notice after the regular message (but before password and link)
		return array_slice( $mail_parts, 0, 1, true ) + $temp + array_slice( $mail_parts, 1, null, true );
	}

	return $mail_parts;
}

add_filter( 'efpic_mail_parts', 'efpic_selection_options_mail_part', 10, 3 );