<?php
/**
 * Everything to do with custom approval forms
 *
 * @since brand-customize (1.6.0)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Display the approval form.
 *
 * @since brand-customize (1.6.0)
 *
 * @see efpic-pro/legacy/efpic-brand-customize/templates/brand-customize-send-selection.php
 */
function efpic_bc_approval_form() {
	// Define default form fields
	$fields = [	
		0 => [
			'type' => 'textarea',
			'label' => __( 'Anything else you want us to know?', 'efpic' ),
			'placeholder' => __( 'Leave a comment…', 'efpic' ),
			'width' => 100,
			'required' => false,
			'value' => '',
			'options' => '',
			'validation' => '',
		]
	];

	// Filter fields 
	$fields = apply_filters( 'efpic_approval_fields', $fields );
	$fields = efpic_bc_validate_approval_fields( $fields );

	// Set default field parameters
	$default = [
		'placeholder' => '',
		'width' => 100,
		'required' => false,
		'value' => '',
	];

	// Make sure the fields contain every required parameter
	$fields = array_map( function( $field ) use ( $default ) { 
		return wp_parse_args( $field, $default );
	}, $fields );

	// Render fields
	foreach( $fields as $field ) {
		efpic_bc_render_form_fields( $field );
	}
}

add_action( 'efpic_approval_form', 'efpic_bc_approval_form' );


/**
 * Validate the approval form fields.
 *
 * @since brand-customize (1.6.0)
 *
 * @param array $fields All the approval form fields
 * @return array The validated fields
 */
function efpic_bc_validate_approval_fields( $fields ) {
	$field_names = [];
	$generic_field_name_num = 1;

	foreach( $fields as $key => $field ) {

		// Validate type field, remove if type is not set
		if ( ! isset( $field['type'] ) OR ! in_array( $field['type'], [ 'paragraph', 'select', 'text', 'textarea' ] ) ) {
			unset( $fields[$key] );
			continue;
		}

		// Remove field, if label is not present
		if ( empty( $field['label'] ) ) {
			unset( $fields[$key] );
			continue;
		}

		// Remove select field, if there are no options
		if ( $field['type'] == 'select' && ( empty( $field['options'] ) OR ! is_array( $field['options'] ) ) ) {
			unset( $fields[$key] );
			continue;
		}

		// Generate field name
		$temp = sanitize_key( $fields[$key]['label'] );
		if ( ! empty( $temp ) ) {
			$fields[$key]['name'] = $temp;
		}
		else {
			$fields[$key]['name'] = wp_rand( 12, false, false );
		}
		// Make sure field name is unique
		if ( in_array( $fields[$key]['name'], $field_names ) ) {
			$fields[$key]['name'] = $fields[$key]['name'] . $generic_field_name_num;
		}

		$field_names[] = $fields[$key]['name'];
		$generic_field_name_num++;
	}

	return $fields;
}


/**
 * Render the individual approval form fields.
 *
 * @since brand-customize (1.6.0)
 *
 * @param array $field The approval form fields
 */
function efpic_bc_render_form_fields( $field ) {
	// Filter field
	$field = apply_filters( 'efpic_pro_approval_field_before_render', $field );

	$enforce_required = true;

	$current_screen = '';
	if ( function_exists( 'get_current_screen' ) ) {
		$current_screen = get_current_screen();
		if ( ! empty( $current_screen->id ) ) {
			$current_screen = $current_screen->id;
		}
	}

	if ( $current_screen == 'efpic_collection' ) {
		$enforce_required = false;
	} 

	if ( isset( $field['break'] ) AND $field['break'] == true ) {
		echo '<p class="break"></p>';
	}

	switch( $field['type'] ) {
		case 'paragraph':
			echo '<p class="paragraph">' . $field['label'] . '</p>';
			break;

		case 'text':
			echo '<p class="col-' . $field['width'] . '">';
			echo '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
			echo '<input type="text" name="efpic-approval-form[' . $field['name'] . ']" id="' . $field['name'] . '" placeholder="' . $field['placeholder'] . '"';
			if ( $field['required'] AND $enforce_required == true ) {
				echo ' required';
			}
			echo ' value="' . $field['value'] . '" />';
			echo '</p>';
			break;

		case 'textarea':
			echo '<p class="col-' . $field['width'] . '">';
			echo '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
			echo '<textarea name="efpic-approval-form[' . $field['name'] . ']" id="' . $field['name'] . '" placeholder="' . $field['placeholder'] . '"';
			if ( $field['required'] AND $enforce_required == true ) {
				echo ' required';
			}
			echo '>' . $field['value'] . '</textarea>';
			echo '</p>';
			break;
 
		case 'select':
			echo '<p class="col-' . $field['width'] . '">';
			echo '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
			echo '<select name="efpic-approval-form[' . $field['name'] . ']" id="' . $field['name'] . '"';
			if ( $field['required'] AND $enforce_required == true ) {
				echo ' required';
			}
			echo '>';
			foreach( $field['options'] as $option ) {
				$option = explode( '|', $option );
				$string = ( empty( $option[1] ) ) ? $option[0] : $option[1];
				echo '<option value="' . $option[0] . '"'. selected( $option[0], $field['value'], false ) . '>' . $string . '</option>';
			}
			echo '</select>';
			break;
	}
}


/**
 * Check if client has sent the form field before, prefill previous value.
 *
 * @since 1.4.6
 *
 * @param array $field The form field.
 * @return array The filtered form field.
 */
function efpic_pro_approval_field_previous_value( $field ) {
	if ( ! empty( $_GET['ident'] ) ) {
		$ident = sanitize_key( $_GET['ident'] );
		$approved_before = get_post_meta( get_the_ID(), '_efpic_collection_selection_' . $ident, true );
		
		// Return if there is no previous approval
		if ( empty( $approved_before['approval_fields'] ) ) {
			return $field;
		}

		// Check if a previous field value exists and set field value accordingly
		if ( array_key_exists( $field['name'], $approved_before['approval_fields'] ) ) {
			$field['value'] = $approved_before['approval_fields'][$field['name']]['value'];
		}
	}

	return $field;
}

add_filter( 'efpic_pro_approval_field_before_render', 'efpic_pro_approval_field_previous_value' );


/**
 * Validate and enforece required fields in the approval form before approving the collection.
 *
 * @since brand-customize (1.6.0)
 *
 * @see efpic/backend/includes/efpic-ajax.php
 *
 * @param array $p The $_POST variable sent via AJAX
 */
function efpic_bc_validate_custom_approval_form( $p ) {

	// Only validate if final approval is the intent
	if ( isset( $p['intent'] ) AND $p['intent'] == 'approve' ) {

		$fields = apply_filters( 'efpic_approval_fields', [] );
		$fields = efpic_bc_validate_approval_fields( $fields );
		$fields_with_error = [];

		foreach( $fields as $key => $field ) {
			if ( isset( $field['required'] ) AND $field['required'] == true ) {
				if ( empty( $p['approval_fields'][$field['name']]['value'] ) ) {
					$fields_with_error[$key] = $field['label'];
				}
			}

			if ( ! empty( $field['validation'] ) ) {
				if ( ! preg_match( "/" . $field['validation'] . "/", $p['approval_fields'][$field['name']]['value'] ) ) {
					$fields_with_error[$key] = $field['label'];
				}
			}
		}

		if ( count( $fields_with_error ) > 0 ) {
			$return = array(
				'message' => __( 'Error: Please review the following fields:', 'efpic-pro' ) . ' ' . implode( ', ', $fields_with_error ),
				'button_text' => __( 'OK', 'efpic' )
			);
			wp_send_json_error( $return );
			exit;
		}
	}
}

add_action( 'efpic_send_selection_validation', 'efpic_bc_validate_custom_approval_form' );


/**
 * Saved custom approval form fields in _efpic_collection_selection post meta.
 * 
 * @since brand-customize (1.6.0)
 *
 * @see efpic/backend/includes/efpic-ajax.php
 *
 * @param array $save The meta data to be saved
 * @param array $p The $_POST variable sent via AJAX
 * @return array The filtered meta data
 */
function efpic_bc_save_selection( $save, $p ) {

	// Only validate if final approval is the intent
	if ( isset( $p['intent'] ) AND $p['intent'] == 'approve' ) {

		// Prepare array to be saved
		$save['approval_fields'] = $p['approval_fields'];
		
		// Sanitize
		if ( ! empty( $p['approval_fields'] ) ) {
			foreach( $p['approval_fields'] as $field_key => $field ) {
				$save['approval_fields'][$field_key]['value'] = trim( implode( "\n", array_map( 'sanitize_text_field', explode( "\n", stripslashes( $field['value'] ) ) ) ) );
			}
		}

		// Use shorter label for the default comment field
		if ( ! empty( $save['approval_fields']['efpic_approval_message']['value'] ) ) {
			$save['approval_fields']['efpic_approval_message']['value'] = trim( implode( "\n", array_map( 'sanitize_text_field', explode( "\n", stripslashes( $p['approval_fields']['efpic_approval_message']['value'] ) ) ) ) );
			$save['approval_fields']['efpic_approval_message']['label'] = __( 'Message', 'efpic' );
		}
	}

	return $save;
}

add_filter( 'efpic_save_selection', 'efpic_bc_save_selection', 10, 2 );