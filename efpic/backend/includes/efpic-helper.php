<?php
/**
 * Efpic Helper functions
 *
 * @since 0.5.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Add additional body classes for admin screens.
 *
 * @since 0.3.2
 */
function efpic_admin_body_class( $admin_body_class ) {

	// Get current admin screen
	$current_screen = get_current_screen();

	// Check if we are on a 'post.php' page
	if ( $current_screen->base == 'post' ) {
		// Add new class to the array;
		$admin_body_class .= ' post-status-' . get_post_status();
	}

	// return the array
	return $admin_body_class;

}

add_filter( 'admin_body_class', 'efpic_admin_body_class' );


/**
 * Make our efpic_theme option overridable by URL parameter 'theme'
 * 
 * @since 2.4.0
 * 
 * @param mixed $theme_option The 'efpic_theme' option from the DB
 * @return mixed $theme The filtered value
 */
function efpic_filter_theme_option( $theme_option ) {

	$theme_parameter = ! empty( $_GET['theme'] ) ? $_GET['theme'] : false;

	switch( $theme_parameter ) {
		case 'light':
			$theme = 'light';
			break;
		case 'dark':
			$theme = 'dark';
			break;
		default:
			$theme = $theme_option;
	}

	return $theme;

}

add_filter( 'option_efpic_theme', 'efpic_filter_theme_option' );


/**
 * Change the post_status.
 *
 * @since 0.3.0
 * 
 * @param int $post_id The collection post ID
 * @param string $status The collection status
 */
function efpic_update_post_status( $post_id, $status ) {
	if ( $status != 'delivered' && $status != 'delivery-draft' && $status != 'sent' && $status != 'approved' && $status != 'expired' && $status != 'draft' ) {
		return $post_id;
	}

	$post_contents = array(
		'ID' => $post_id,
		'post_status' => $status
	);

	// Remove our mail and save function so we don't get trapped in a loop
	remove_action( 'save_post_efpic_collection', 'efpic_collection_publish' );
	remove_action( 'save_post_efpic_collection', 'efpic_messaging_logic' );

	// Update the post, which calls save_post again
	wp_update_post( $post_contents );

	// Re-add the function for mail and save after save_post has fired
	add_action( 'save_post_efpic_collection', 'efpic_collection_publish', 10, 2 );
	add_action( 'save_post_efpic_collection', 'efpic_messaging_logic', 10, 2 );
}


/**
 * Publish collection - When using "copy link & send manually" share option.
 *
 * @since 1.7.0
 *
 * @param int $post_id The collection post ID
 * @param object $post The post object
 * @return int The collection post ID
 */
function efpic_collection_publish( $post_id, $post ) {
	// Only go ahead if our button was clicked
	if ( ! isset( $_POST['efpic_sendmail'] ) )
		return $post_id;

	// Check if nonce is set
	if ( ! isset( $_POST['efpic_collection_metabox_nonce'] ) )
		return $post_id;

	// Verify nonce
	if ( ! wp_verify_nonce( $_POST['efpic_collection_metabox_nonce'], 'efpic_collection_metabox' ) )
		return $post_id;

	// Abort if no title is set
	if ( ! $post->post_title )
		return $post_id;

	// Abort if there are no proof images, but the intent is proofing
	if ( isset( $_POST['efpic_gallery_ids'] ) AND empty( $_POST['efpic_gallery_ids'] ) ) {
		return $post_id;
	}

	// Abort if there are no delivery images, but the intent is delivery – and the delivery option is upload
	if ( isset( $_POST['delivery_image_ids'] ) AND empty( $_POST['delivery_image_ids'] ) AND $_POST['efpic_delivery_option'] == 'upload' ) {
		return $post_id;
	}

	// Abort if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;

	// Abort if the user doesn't have permissions
	if ( ! current_user_can( efpic_capability(), $post_id ) )
		return $post_id;

	// Abort sending if there are error notifications
	$notifications = get_option( '_' . get_current_user_id() . '_efpic_notifications' );

	if ( is_array( $notifications ) ) {
		foreach( $notifications as $notification ) {
			if ( strpos( $notification['type'], 'error' ) )  {
				return $post_id;
			}
		}
	}

	// Only send mail, if this method is selected
	if ( $_POST['efpic_collection_share_method'] == 'efpic-copy-link' ) {

		if ( $post->post_status == 'delivery-draft' ) {
			// Set the post status to "sent"
			efpic_update_post_status( $post_id, 'delivered' );
			// Update collection history
			efpic_update_collection_history( $post_id, 'delivery-published' );

			// Add success notification
			efpic_add_notification( 'efpic_mail_sent', 'notice notice-success is-dismissible', __( 'Your delivery is ready! Make sure to send the link to your client:', 'efpic' ) . ' <input type="text" value="' . get_draft_permalink( $post_id ) . '" />' );
		}
		else {
			// Set the post status to "sent"
			efpic_update_post_status( $post_id, 'sent' );
			// Update collection history
			efpic_update_collection_history( $post_id, 'published' );

			// Add default client, if none exists
			$create_default_client = apply_filters( 'efpic_create_default_client', true, $post_id );

			if ( $create_default_client && ! efpic_collection_has_ident( $post_id ) ) {
				efpic_add_client_to_hashes( $post_id, __( 'Client', 'efpic') );
			}

			// Add success notification
			efpic_add_notification( 'efpic_mail_sent', 'notice notice-success is-dismissible', __( 'The collection is ready! Make sure to send the link to your client:', 'efpic' ) . ' <input type="text" value="' . get_draft_permalink( $post_id ) . '" />' );
		}
	}
}

add_action( 'save_post_efpic_collection', 'efpic_collection_publish', 10, 2 );


/**
 * Update efpic collection history.
 *
 * @since 0.9.4
 * @since 2.3.5 Added $meta param.
 *
 * @param $post_id
 * @param $event, string - sent, reopened, approved
 * @param $data, string or array - additional data
 * @param array $meta Additional data about the history event.
 */
function efpic_update_collection_history( $post_id, $event, $data = NULL, $meta = [] ) {
	// Load existing history
	$existing_history = get_post_meta( $post_id, '_efpic_collection_history', true );

	// Create new history array
	$time = time();
	if ( is_array( $existing_history ) && array_key_exists( $time, $existing_history ) ) {
		$time++;
	}
	$new_history["$time"] = array(
		'event' => $event,
		'data' => $data
	);

	if ( ! is_array( $meta ) ) {
		$meta = [ $meta ];
	}

	if ( ! empty( $meta ) ) {
		$new_history["$time"]['meta'] = $meta;
	}

	// Merge arrays
	if ( is_array( $existing_history ) ) {
		$history = $existing_history + $new_history; // Using +, because `array_merge()` will reindex
	}
	else {
		$history = $new_history;
	}

	// Save updated history
	update_post_meta( $post_id, '_efpic_collection_history', $history );

	// Prevent infinite loop
	remove_action( 'save_post_efpic_collection', 'efpic_collection_publish' );
	remove_action( 'save_post_efpic_collection', 'efpic_messaging_logic' );

	// Update modified time
	$date = date( 'Y-m-d H:i:s', time() );
	wp_update_post( [
		'ID' => $post_id,
		'post_modified' => $date,
		'post_modified_gmt' => get_gmt_from_date( $date ),
	] );
}


/**
 * Get efpic collection history event time.
 *
 * @since 0.9.4
 *
 * @param $post_id
 * @param $event, string - sent, reopened, approved
 */
function efpic_get_collection_history_event_time( $post_id, $event ) {

	$efpic_collection_history = get_post_meta( $post_id, '_efpic_collection_history', false );

	// Check if history exists and if it contains anything
	if ( is_array( $efpic_collection_history ) AND 0 < count( $efpic_collection_history ) ) {

		// Get timestamps
		$keys = array_keys( $efpic_collection_history[0] );

		// Check at which timestamp our event existing_history
		foreach( $efpic_collection_history[0] as $key => $temp ) {

			// Check all events, get the most recent one
			if ( isset( $temp['event'] ) AND $event == $temp['event'] ) {
				$time = $key; // The final time will be the last $event in the history
			}
		}

		// Check if it is a valid timestamp
		if ( isset( $time ) AND is_numeric( $time ) ) {
			return $time;
		}
	}

	return false;
}


/**
 * Get last history event.
 *
 * @since 1.5.7
 *
 * @param int $post_id The collection post ID
 * @param int $time A timestamp
 */
function efpic_get_last_history_event( $post_id, $time = null ) {

	$event = '';
	$efpic_collection_history = get_post_meta( $post_id, '_efpic_collection_history', true );

	if ( is_array( $efpic_collection_history ) ) {

		if ( ! $time ) {
			end( $efpic_collection_history );
			$time = key( $efpic_collection_history );
		}

		if ( ! empty( $efpic_collection_history[$time]['event'] ) ) {
			$event = $efpic_collection_history[$time]['event'];
		}
	}

	if ( empty( $event ) ) {
		return 'last-modified';
	}

	return $event;
}


/**
 * Check if a collection has been approved/closed/expired in the past.
 *
 * @since 1.4.4
 *
 * @param int $collection_id The collection post ID
 *
 * @return bool Whether the collection has been approved/closed/expired before
 */
function efpic_has_collection_been_closed( $collection_id ) {
	$events = [];
	$collection_history = get_post_meta( $collection_id, '_efpic_collection_history', true );

	if ( is_array( $collection_history ) AND ! empty( $collection_history ) ) {
		foreach( $collection_history as $event ) {
			$events[] = $event['event'];
		}

		if ( array_intersect( [ 'approved', 'closed-manually', 'expired' ], $events ) ) {
			return true;
		}
	}

	return false;
}


/**
 * Display a nice name for a collection event.
 *
 * @since 2.2.0
 *
 * @param string $event The collection event
 * @return string The collection event nice name
 */
function efpic_collection_event_prettify( $event ) {
	switch( $event ) {
		case 'sent':
			return __( 'Sent to client(s)', 'efpic' );
			break;
		case 'sent-to-new-client':
			return __( 'Sent to additional client', 'efpic' );
			break;
		case 'published':
			return __( 'Published', 'efpic' );
			break;
		case 'new-client-registered':
			return __( 'New client registered', 'efpic' );
			break;
		case 'removed-client':
			return __( 'Removed client', 'efpic' );
			break;
		case 'approved':
			return __( 'Approved', 'efpic' );
			break;
		case 'approved-by-client':
			return __( 'Approved by client', 'efpic' );
			break;
		case 'reopened-for-client':
			return __( 'Reopened for client', 'efpic' );
			break;
		case 'reopened':
			return __( 'Reopened', 'efpic' );
			break;
		case 'reopened-to-draft':
			return __( 'Reverted to draft', 'efpic' );
			break;
		case 'reopened-to-delivery-draft':
			return __( 'Reverted to delivery draft', 'efpic' );
			break;
		case 'expired':
			return __( 'Expired', 'efpic' );
			break;
		case 'closed-manually':
			return __( 'Closed manually', 'efpic' );
			break;
		case 'preparing-delivery':
			$event = __( 'Preparing Delivery', 'efpic' );
			break;	
		case 'delivered':
			$event = __( 'Delivered', 'efpic' );
			break;
		case 'delivery-published':
			$event = __( 'Delivery published', 'efpic' );
			break;
		case 'last-modified':
			return __( 'Last modified', 'efpic' );
			break;
		case 'images-updated':
			return __( 'Images updated', 'efpic' );
			break;
	}

	return $event;
}
 

/**
 * Get an array of collection image IDs.
 *
 * @since 1.10.0
 * @since 2.3.0 Switch between regular and delivery collections.
 *
 * @param int $collection_id The collection post ID
 * @return array The collection image IDs or an empty array
 */
function efpic_get_collection_images( $collection_id ) {
	$type = 'gallery';
	if ( in_array( get_post_status( $collection_id ),  [ 'delivery-draft', 'delivered' ] ) ) {
		$type = 'delivery';
	}

	$images = explode( ',' , get_post_meta( $collection_id, '_efpic_collection_' . $type . '_ids', true ) );

	if ( ! empty( $images[0] ) ) {
		return $images;
	}
	
	return [];
}


/**
 * Get the number of images in a collection.
 *
 * @since 1.6.2
 * @since 1.10.0 Using the new `efpic_get_collection_images` function
 *
 * @param $collection_id The collection post ID
 * @return int Number of images in a collection
 */
function efpic_get_collection_image_num( $collection_id ) {
	$images = efpic_get_collection_images( $collection_id );

	if ( ! empty( $images[0] ) ) {
		return count( $images );
	}

	return 0;
}


/**
 * Get the number of selected images of a collection.
 *
 * @since 1.3.4
 *
 * @param $post_id - The collection post ID
 * @return int Number of selected images (at least once for multi client collections)
 */
function efpic_get_selection_count( $post_id ) {

	// For multi client collections
	$efpic_collection_hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );

	if ( ! empty( $efpic_collection_hashes ) ) {

		$image_ids = [];

		foreach( $efpic_collection_hashes as $key => $hash ) {
			$selection = get_post_meta( $post_id, '_efpic_collection_selection_' . $key, true );
			if ( ! empty( $selection['selection'] ) ) {
				$image_ids = array_merge( $image_ids, $selection['selection'] );
			}
		}

		return count( array_unique( $image_ids ) );
	}

	// For single collections
	else {
		$selection = get_post_meta( $post_id, '_efpic_collection_selection', true );
		return ( isset( $selection['selection'] ) AND is_array( $selection['selection'] ) ) ? count( $selection['selection'] ) : 0;
	}
	
}


/**
 * Get selected images.
 *
 * @since 2.3.4
 *
 * @param int $post_id The collection post ID
 * @param string $ident Identification hash for a multi client collection
 * @param bool $all Return selected at least once or selected by all
 * @return array List of selected image IDs
 */
function efpic_get_selected_images( $post_id, $ident = '', $all = false ) {
	// Prepare variable
	$selection_image_ids = [];

	// Get hashes
	$efpic_collection_hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );

	if ( empty( $efpic_collection_hashes ) ) {
		return [];
	}

	// Check if we handle one client only
	if ( ! empty( $ident ) AND ( ! is_array( $efpic_collection_hashes ) OR ! array_key_exists( $ident, $efpic_collection_hashes ) ) ) {
		return [];
	}

	// Get ids for a certain identity
	// First, check if the identity exists
	if ( ! empty( $ident ) AND array_key_exists( $ident, $efpic_collection_hashes ) ) {
		// Next, check if there is a selection for this identity
		$selection = get_post_meta( $post_id, '_efpic_collection_selection_' . $ident, true );
		if ( ! empty( $selection['selection'] ) ) {
			// Fill filenames
			$selection_image_ids = array_merge( $selection_image_ids, $selection['selection'] );
		}
	}
	// Get ids for all identities
	else {
		// Get selected by all
		if ( $all == true ) {
			$start = true;

			foreach( $efpic_collection_hashes as $key => $hash ) {
				$selection = get_post_meta( $post_id, '_efpic_collection_selection_' . $key, true );

				if ( ! empty( $selection['selection'] ) ) {
					// Fill filenames
					if ( $start == true ) {
						$selection_image_ids = $selection['selection'];
						$start = false;
					}
					else {
						$selection_image_ids = array_intersect( $selection_image_ids, $selection['selection'] );
					}
				}
				
			}
		}
		// Get selected at least once
		else {
			// Iterate through hashes and get selections
			foreach( $efpic_collection_hashes as $key => $hash ) {
				// Fill individual selections
				$selection = get_post_meta( $post_id, '_efpic_collection_selection_' . $key, true );
				if ( ! empty( $selection['selection'] ) ) {
					// Fill filenames
					$selection_image_ids = array_merge( $selection_image_ids, $selection['selection'] );
				}
			}
		}
	}

	$selection_image_ids = array_unique( $selection_image_ids );

	return $selection_image_ids;
}


/**
 * Get approved filenames.
 *
 * @since 1.5.0
 * @since 1.11.0 Add $specialchars param
 *
 * @param int $post_id The collection post ID
 * @param string $ident Identification hash for a multi client collection
 * @param bool $all Return selected at least once or selected by all
 * @param bool $convert Whether filename characters should be converted to HTML entities
 * @return string Filenames of approved images
 */
function efpic_get_approved_filenames( $post_id, $ident = '', $all = false, $convert = true ) {
	$img_filenames = '';

	// Get selected images
	$selection_image_ids = efpic_get_selected_images( $post_id, $ident, $all );

	// Get filenames
	$filename_separator = ( defined( 'EFPIC_FILENAME_SEPARATOR' ) ) ? EFPIC_FILENAME_SEPARATOR : ' ';
	$filename_separator = apply_filters( 'efpic_filename_separator', $filename_separator );

	if ( ! empty( $selection_image_ids ) ) {
		// Loop through our IDs to get the filenames
		foreach ( $selection_image_ids as $selection_image_key => $selection_image_id ) {
			$img_filename = efpic_get_image_filename( $selection_image_id );
			$img_filename = apply_filters( 'efpic_approved_filename', $img_filename, $selection_image_id );

			if ( $selection_image_key !== array_key_last( $selection_image_ids ) ) {
				// Add filename to our string, separated by our separator
				$img_filenames .= $img_filename . $filename_separator;
			} else {
				// No separator after the last filename
				$img_filenames .= $img_filename;
			}
		}

		$img_filenames = trim( $img_filenames );
	}

	if ( $convert === true ) {
		$img_filenames = htmlspecialchars( $img_filenames );
	}

	return $img_filenames;
}


/**
 * Return image filename.
 *
 * @since 3.1.0
 *
 * @param int $attachment_id The attachment post ID
 * @return string The filename
 */
function efpic_get_image_filename( $attachment_id ) {
	// Check for our custom post meta entry first
	$filename = get_post_meta( $attachment_id, '_efpic_original_filename', true );
	// Remove 
	$filename = pathinfo( $filename, PATHINFO_FILENAME );

	// Fallback to the filename after being uploaded
	if ( empty( $filename ) ) {
		$attachment_src = wp_get_attachment_image_src( $attachment_id, 'full' );
		$filename = pathinfo( $attachment_src[0], PATHINFO_FILENAME );
	}

	return $filename;
}


/**
 * Create proof txt file.
 *
 * @since 1.5.0
 *
 * @param int $post_id The collection post ID
 * @param bool $save_file Whether the file or the path should be returned
 * @return string Either the file path or the file will be returned directly
 */
function efpic_create_proof_file( $post_id, $save_file = false ) {
	// Get title
	$title = get_the_title( $post_id );

	// Get client email address
	$email = efpic_get_collection_clients( $post_id );
	$email = implode( ', ', $email );

	// Get the last event
	$last_event = efpic_get_last_history_event( $post_id );

	if ( $last_event == 'approved' || $last_event == 'approved-by-client' ) {
		// Get approval date
		$temp_approval_date = efpic_get_collection_history_event_time( $post_id, 'approved' );
		if ( ! empty( $temp_approval_date ) ) {
			$approval_time = wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $temp_approval_date );
		}
	}

	if ( $last_event == 'expired' ) {
		// Get expired date
		$temp_expired_date = efpic_get_collection_history_event_time( $post_id, 'expired' );
		if ( ! empty( $temp_expired_date ) ) {
			$expired_time = wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $temp_expired_date );
		}
	}
	
	if ( $last_event == 'closed-manually' ) {
		// Get closing date
		$temp_closed_date = efpic_get_collection_history_event_time( $post_id, 'closed-manually' );
		if ( ! empty( $temp_closed_date ) ) {
			$closed_time = wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $temp_closed_date );
		}
	}

	// Get image ids as an array
	$image_ids = efpic_get_selected_images( $post_id, '', false );
	$approved_image_ids = efpic_get_selected_images( $post_id, '', true );

	// Get filenames
	$img_filenames = efpic_get_approved_filenames( $post_id, '', false, false );

	// Get hashes
	$efpic_collection_hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );

	$proof_file_content = '# ' . sprintf( __( 'Selection summary for "%s"', 'efpic' ), $title );

	/* translators: %s: date and time */
	if ( ! empty( $approval_time ) ) {
	$proof_file_content .= "\n\n" . sprintf( __( 'Approved: %s', 'efpic' ), $approval_time );
	}
	elseif ( ! empty( $expired_time ) ) {
		$proof_file_content .= "\n\n" . sprintf( __( 'Expired: %s', 'efpic' ), $expired_time );
	}
	elseif ( ! empty( $closed_time ) ) {
		$proof_file_content .= "\n\n" . sprintf( __( 'Closed: %s', 'efpic' ), $closed_time );
	}

	if ( ! empty( $email ) ) {
		/* translators: %s: email address */
		$proof_file_content .= "\n\n" . sprintf( __( 'Clients: %s','efpic' ), $email );
	}

	/**
	 * Create and return file for multi client collections
	 */ 
	if ( ! empty( $efpic_collection_hashes ) ) {

		$client_selections = [];
		$recipient_num = count( $efpic_collection_hashes );

		// Iterate through multi clients
		foreach( $efpic_collection_hashes as $key => $hash ) {

			$selection = get_post_meta( $post_id, '_efpic_collection_selection_' . $key, true );

			$approval_fields = [];

			// Legacy: Add the old approval message to the approval fields array
			if ( ! empty( $selection['approval_message'] ) ) {
				$approval_fields['efpic_approval_message'] = [
					'label' => __( 'The following comment was added on approval', 'efpic' ),
					'value' => $selection['approval_message'],
				];
			}

			if ( ! empty( $selection['approval_fields'] ) ) {
				$approval_fields = $selection['approval_fields'];
			}

			$client_selection = get_post_meta( $post_id, '_efpic_collection_selection_' . $key, true );
			if ( ! empty( $client_selection['time'] ) ) {
				$time = wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $client_selection['time'] );
			}
			else {
				$time = false;
			}

			$client_selections[] = [
				'name' => $hash['name'],
				'email' => $hash['email'],
				'status' => $hash['status'],
				'time' => $time,
				'approval_fields' => $approval_fields,
				'filenames' => efpic_get_approved_filenames( $post_id, $key, false, false ),
				'num' => count( efpic_get_selected_images( $post_id, $key ) ),
			];

		}

		if ( $recipient_num > 1 ) {
	$proof_file_content .= '.

* * *

';

$proof_file_content .= '## ' . sprintf(
	/* translators: %d: number of selected image(s) */
	_n( 'Selected at least once (%d image):', 'Selected at least once (%d images):', count( $image_ids ), 'efpic' ),
	count( $image_ids )
) .'

' . $img_filenames . '

* * *

## ' . sprintf(
	/* translators: %d: number of selected image(s) */
	_n( 'Selected by all (%d image):', 'Selected by all (%d images):', count( $approved_image_ids ), 'efpic' ),
	count( $approved_image_ids )
) . '

' .  efpic_get_approved_filenames( $post_id, $ident = '', true, false );
		}

		$proof_file_content .= '
';

		if ( ! empty( $client_selections ) ) {
			foreach( $client_selections as $client ) {
				$client_name = efpic_combine_name_email( $client['name'], $client['email'] );
$proof_file_content .= '
* * *';

// Check whether the client has actually approved the selection
if ( $client['status'] == 'approved' ) {
$proof_file_content .= '

' . /* translators: %1$s = client name/email; %2$s = approval date/time */
sprintf( __( '%1$s approved the collection on %2$s.', 'efpic' ), $client_name, $client['time'] );
}
else {
	$proof_file_content .= '

' . /* translators: %s = client name/email */
sprintf( __( '%s has not finally approved the collection.', 'efpic' ), $client_name );
}

		if ( empty( $client['filenames'] ) ) {
			$proof_file_content .= '

' . __( 'No selected images.', 'efpic' ) .'
';
		}
		else {
$proof_file_content .= '

';

$proof_file_content .= sprintf(
	/* translators: %d: number of selected image(s) */
	_n( 'Selected (%d image):', 'Selected (%d images):', $client['num'], 'efpic' ),
	$client['num']
) . '

' . $client['filenames'] . '

'; }

				// Add custom approval fields
				if ( ! empty( $client['approval_fields'] ) ) {
$proof_file_content .= efpic_custom_fields_in_proof_file( $client['approval_fields'] );
				}

			}
		}
	}

	/**
	 * Create and return file for single client collections
	 */ 
	else {

		// Get client comment
		$efpic_collection_selection = get_post_meta( $post_id, '_efpic_collection_selection', true );

$proof_file_content .= '.

* * *

';

		// Legacy: Add the old approval message to the approval fields array
		if ( ! empty( $efpic_collection_selection['approval_message'] ) ) {
			$efpic_collection_selection['approval_fields']['efpic_approval_message'] = [
				'label' => __( 'The following comment was added on approval', 'efpic' ),
				'value' => $efpic_collection_selection['approval_message'],
			];
		}

		// Add custom approval fields
		if ( ! empty( $efpic_collection_selection['approval_fields'] ) ) {
$proof_file_content .= efpic_custom_fields_in_proof_file( $efpic_collection_selection['approval_fields'] );
$proof_file_content .= '
* * *

';
		}

$proof_file_content .= __( 'Selected images', 'efpic' ) .':

' . $img_filenames;

	}

	// Filter file content
	$proof_file_content = apply_filters( 'efpic_proof_file_content', $proof_file_content, $post_id );

	// Filter and sanitize file name
	/* translators: File name (prefix) for approved collections */
	$proof_file_name = apply_filters( 'efpic_proof_file_name', __( 'selection', 'efpic' ) . '-' . sanitize_title( $title ) . '.txt', $post_id );
	$proof_file_name = sanitize_file_name( $proof_file_name );

	// Save the file
	if ( $save_file === true ) {
		$upload_dir = wp_get_upload_dir();
		$full_path = $upload_dir['basedir'] . '/efpic/collections/' . $post_id . '/' . $proof_file_name;

		file_put_contents( $full_path, $proof_file_content );

		return $full_path;
	}

	// Open the file
	header( 'Content-Type: application/download' );
	header( 'Content-Disposition: attachment; filename="' . $proof_file_name . '"' );
	echo $proof_file_content;
	exit;
}


/**
 * Add approval fields into the proof file.
 *
 * @since 1.6.5
 *
 * @param array $approval_fields The approval fields
 * @return string Approval fields with labels and values
 */
function efpic_custom_fields_in_proof_file( $approval_fields ) {
	$text = '';
	$num = count( $approval_fields );
	$i = 1;
	foreach( $approval_fields as $field ) {
		if ( ! empty( $field['value'] ) ) {
			if ( $num == 1 ) {
				$text .= "\n";
			}
			$text .= $field['label'] . ":\n" . $field['value'] . "\n";
			if ( $num != $i ) {
				$text .= "\n";
			}
		}
		$i++;
	}

	return $text;
}


/**
 * Check whether an ident exists in a collection.
 *
 * @since 2.3.0
 *
 * @param string $ident The identification string
 * @param int $collection_id The collection post ID
 * @return bool Whether the ident exists
 */
function efpic_ident_exists( $ident, $collection_id ) {
	$hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );
	if ( is_array( $hashes ) && array_key_exists( $ident, $hashes ) ) {
		return true;
	}

	return false;
}


/**
 * Check if hashes exist for a collection.
 *
 * @since 2.3.0
 *
 * @param int $collection_id The collection post ID.
 * @return bool Whether hashes exist for this collection.
 */
function efpic_collection_has_ident( $collection_id ) {
	$hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );
	if ( ! empty( $hashes ) ) {
		return true;
	}

	return false;
}


/**
 * Get email address from ident parameter.
 *
 * @since 1.7.0
 *
 * @param int $collection_id The collection post ID
 * @param string $ident The identification string
 * @return string|bool Email address or false
 */
function efpic_get_email_from_ident( $collection_id, $ident ) {
	$hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );

	if ( ! empty( $hashes[$ident]['email'] ) ) {
		$email = sanitize_email( $hashes[$ident]['email'] );
		return $email;
	}
	return false;
}


/**
 * Get status from ident parameter.
 *
 * @since 2.3.0
 *
 * @param int $collection_id The collection ID
 * @param string $ident The identification string
 * @return string|bool Status or false
 */
function efpic_get_status_from_ident( $collection_id, $ident ) {
	$hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );

	if ( ! empty( $hashes[$ident]['status'] ) ) {
		$status = sanitize_text_field( $hashes[$ident]['status'] );
		return $status;
	}

	return false;
}


/**
 * Get name from ident parameter.
 *
 * @since 2.3.0
 *
 * @param int $collection_id The collection post ID
 * @param string $ident The identification string
 * @return string|bool Name or false
 */
function efpic_get_name_from_ident( $collection_id, $ident ) {
	$hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );

	if ( ! empty( $hashes[$ident]['name'] ) ) {
		$email = sanitize_text_field( $hashes[$ident]['name'] );
		return $email;
	}

	return false;
}


/**
 * Get ident parameter from email address.
 *
 * @since 1.7.1
 *
 * @param int $collection_id The collection post ID
 * @param string $email Email address
 * @return string|bool The identification string or false
 */
function efpic_get_ident_from_email( $collection_id, $email ) {
	$hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );

	if ( ! empty( $hashes ) && is_array( $hashes ) ) {
		foreach( $hashes as $ident => $hash ) {
			if ( $hash['email'] == $email ) {
				return $ident;
			}
		}
	}

	return false;
}


/**
 * Get email addresses for a collection.
 *
 * @since 1.7.5
 *
 * @param int $post_id The collection post ID
 * @return array The email addresses
 */
function efpic_get_collection_emails( $post_id ) {
	$emails = [];

	$collection_hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );

	if ( ! empty( $collection_hashes ) ) {
		foreach ( $collection_hashes as $hash => $hash_fields ) {
			$emails[] = $hash_fields['email'];
		}

		// Remove empty entries
		$emails = array_filter( $emails );
	}

	return $emails;
}


/**
 * Get all clients for a collection.
 *
 * @since 2.3.0
 *
 * @param int $post_id The collection post ID
 * @return array The clients (name + email)
 */
function efpic_get_collection_clients( $post_id ) {
	$clients = [];

	$collection_hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );
	foreach( $collection_hashes as $hash => $hash_fields ) {
		$clients[] = efpic_combine_name_email( $hash_fields['name'], $hash_fields['email'] );
	}

	// Remove empty entries
	$clients = array_filter( $clients );

	return $clients;
}


/**
 * Check if all clients have approved their selections.
 *
 * @since 2.3.5
 *
 * @param int $collection_id The collection post ID.
 * @return bool Whether everyone has approved the collection.
 */
function efpic_have_all_clients_approved( $collection_id ) {
	$hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );

	if ( is_array( $hashes ) && ! empty( $hashes ) ) {
		$all_approved = true;

		foreach( $hashes as $ident => $value ) {
			if ( efpic_get_status_from_ident( $collection_id, $ident ) != 'approved' ) {
				$all_approved = false;
			}
		}

		return $all_approved;
	}

	return false;
}


/**
 * Update client email address history.
 *
 * @since 1.7.5
 *
 * @param string $mail_context Info about which email we are hooking into
 * @param int $post_id The collection post ID
 */
function efpic_update_email_history( $mail_context, $post_id ) {

	// Allow users to turn email history saving off
	if ( ! apply_filters( 'efpic_save_email_history', true ) ) {
		return;
	}

	// Only run this one time (see below)
	if ( did_action( 'efpic_update_email_history_once' ) >= 1 ) {
		return;
	}

	if ( $mail_context == 'client_collection_new' ) {

		// Get email(s)
		$emails = efpic_get_collection_emails( $post_id );

		// Get history
		$history = get_user_option( 'efpic_email_history' );

		if ( empty( $history ) ) {
			$history = [];
		}

		if ( ! empty( $emails ) ) {

			foreach( $emails as $email ) {
				// Check if email is already in there, if so, add to the count
				$search = array_search( $email, array_column( $history, 'email' ) );
				if ( false !== $search ) {
					$history[$search]['uses']++;
				}
				else {
					// If it is a new address, add it to the array
					array_push( $history, [ 'email' => $email, 'uses' => 1 ] );
				}
			}

			// Sort array by uses
			usort( $history, function( $a, $b ) {
				return $b['uses'] <=> $a['uses'];
			});

			update_user_option( get_current_user_id(), 'efpic_email_history', $history );
		}
	}
	
	// We need to allow the "parent" action (efpic_after_email_sent) to run more than once.
	// Problem: It runs once per email address / email sent.
	// So we use this "helper" action to run this function only once.
	do_action( 'efpic_update_email_history_once' );
}

add_action( 'efpic_after_email_sent', 'efpic_update_email_history', 10, 2 );


/**
 * The user's email history as a datalist.
 *
 * @since 1.7.5
 */
function efpic_the_email_history_datalist() {

	// Allow users to turn email suggestions off
	if ( ! apply_filters( 'efpic_use_email_history', true ) ) {
		return;
	}

	$history = get_user_option( 'efpic_email_history' );
	if ( empty( $history ) ) {
		return;
	}

	$datalist = '<datalist id="email-history">';
	foreach( $history as $entry ) {
		$datalist .= '<option value="' . $entry['email'] . '">';
	}
	$datalist .= '</datalist>';

	/**
	 * Filters email history datalist output
	 *
	 * @param string $datalist The datalist that will be printed
	 * @param array $history The array containing the email history
	 * @since 1.7.5
	 */
	echo apply_filters( 'efpic_email_history_list', $datalist, $history );
}


/**
 * Get the number of recipients for a collection.
 *
 * @since 2.1.0
 *
 * @param int $post_id The collection post ID
 * @return int The number of recipients
 */
function efpic_get_recipients_num( $post_id ) {
	$hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );
	if ( ! empty( $hashes ) AND count( $hashes ) > 0 ) {
		return count( $hashes );
	}

	return 0;
}


/**
 * Return the default expiration length.
 *
 * @since 2.0.0
 *
 * @return int The number of days
 */
function efpic_expiration_length() {
	// Add filter to adjust the expiration length, defaults to 30 days
	$expiration_length = (int) apply_filters( 'efpic_expiration_length', 30 );

	// Make sure the number is positive
	$expiration_length = abs( $expiration_length );

	// Make sure the number is at least 1
	if ( $expiration_length < 1 ) { $expiration_length = 1; }

	return $expiration_length;
}


/**
 * Calculate expiration time starting from now.
 *
 * @since 2.0.0
 *
 * @since int $expiration_length Number of days
 */
function efpic_calculate_expiration_time() {
	// Get expiration length
	$expiration_length = efpic_expiration_length();

	// Get the current timestamp
	$current_timestamp = ceil( time() / 300 ) * 300;

	// Calculate the timestamp for 30 days from now (30 days * 24 hours * 60 minutes * 60 seconds)
	$new_timestamp = $current_timestamp + ( $expiration_length * 24 * 60 * 60 );

	return $new_timestamp;
}


/**
 * Add action to expire collections
 *
 * @since 2.0.0
 */
add_action( 'efpic_collection_checker', 'efpic_expire_collections' );


/**
 * Adds a custom cron schedule for every 5 minutes.
 *
 * @since 2.0.0
 *
 * @param array $schedules An array of non-default cron schedules.
 * @return array Filtered array of non-default cron schedules.
 */
function efpic_add_custom_cron_schedule( $schedules ) {
	$schedules[ 'every-5-minutes' ] = [
		'interval' => 300,
		'display' => __( 'Every 5 minutes', 'efpic' )
	];
	return $schedules;
}

add_filter( 'cron_schedules', 'efpic_add_custom_cron_schedule' );


/**
 * Schedule collection checker.
 *
 * @since 2.0.0
 */
if ( ! wp_next_scheduled( 'efpic_collection_checker' ) ) {
	$now = time();
	$next = ceil( $now / 300 ) * 300; // We want exactly every five minutes. :)
	wp_schedule_event( $next, 'every-5-minutes', 'efpic_collection_checker' );
}


/**
 * Expire collections.
 *
 * @since 2.0.0
 */
function efpic_expire_collections() {
	// Get sent collections, where the expiration time is in the past
	$args = [
		'post_type' => 'efpic_collection',
		'post_status' => [ 'sent' ],
		'posts_per_page' => -1,
		'fields' => 'ids',
		'meta_query' => [
			'expiration' => [
				'key' => '_efpic_collection_expiration_time',
				'value' => time(),
				'compare' => '<',
			],
		],
	];

	$collections = get_posts( $args );

	if ( ! empty( $collections ) ) {
		foreach( $collections as $collection_id ) {
			// Set the status to expired
			efpic_update_post_status( $collection_id, 'expired' );
			// Update collection history
			efpic_update_collection_history( $collection_id, 'expired' );
			// Run action after a collection has expired
			do_action( 'efpic_collection_has_expired', $collection_id );
		}
	}
}


/**
 * Maybe set individual clients to failed when collection expires.
 *
 * @since 2.2.0
 *
 * @param int $post_id The collection post ID
 * @param bool $preview Wether the function is used for preview
 */
function efpic_collection_maybe_fail_clients( $collection_id ) {
	$clients = get_post_meta( $collection_id, '_efpic_collection_hashes', true );
	foreach( $clients as $hash => $client ) {
		if ( $clients[$hash]['status'] == 'sent' ) {
			$clients[$hash]['status'] = 'failed';
		}
	}

	update_post_meta( $collection_id, '_efpic_collection_hashes', $clients );
}

add_action( 'efpic_collection_has_expired', 'efpic_collection_maybe_fail_clients' );
add_action( 'efpic_collection_has_closed', 'efpic_collection_maybe_fail_clients' );

/**
 * Add action to send reminders for collections with open/unapproved selections
 *
 * @since 2.0.0
 */
add_action( 'efpic_collection_checker', 'efpic_maybe_send_selection_reminder' );


/**
 * Decide whether to send a selection reminder.
 * 
 * @since 2.0.0
 */
function efpic_maybe_send_selection_reminder() {
	if ( get_option( 'efpic_send_reminder' ) != 'on' ) {
		return;
	}

	$args = [
		'post_type' => 'efpic_collection',
		'post_status' => [ 'sent' ],
		'posts_per_page' => -1,
		'fields' => 'ids',
		// Check for both keys, to make sure the collection has recipients
		'meta_query' => [
			'has_selection' => [
				'compare_key' => 'LIKE',
				'key' => '_efpic_collection_selection_',
			],
			'has_hashes' => [
				'key' => '_efpic_collection_hashes',
				'compare' => 'EXISTS'
			]
		]
	];

	$collections = get_posts( $args );

	if ( ! empty( $collections ) ) {
		foreach( $collections as $collection_id ) {
			$recipients = get_post_meta( $collection_id, '_efpic_collection_hashes', true );
			foreach( $recipients as $recipient_id => $recipient_data ) {
				// Get selection data for recipient
				$selection_data = get_post_meta( $collection_id, '_efpic_collection_selection_' . $recipient_id, true );

				// Check if the recipient has any selection data and not yet approved the collection
				if ( ! empty( $selection_data ) && efpic_get_status_from_ident( $collection_id, $recipient_id ) == 'sent' ) {
					// Skip, if a reminder has been sent before
					$reminder = get_post_meta( $collection_id, '_efpic_collection_reminder_' . $recipient_id, true );
					if ( ! empty( $reminder ) ) {
						continue;
					}

					// Get time of the last selection update
					$time_difference = apply_filters( 'efpic_selection_reminder_time_diff', 86400 );
					// Check if the selection update is more than the time difference
					if ( $selection_data['time'] < time() - $time_difference ) {
						// Send reminder
						do_action( 'efpic_send_selection_reminder', $collection_id, $recipient_id, $recipient_data['email'] );
						// Set reminder post meta
						update_post_meta( $collection_id, '_efpic_collection_reminder_' . $recipient_id, time() );
					}
				}
			}
		}
	}
}


/**
 * Combine client name and email address.
 *
 * @since 2.3.0
 *
 * @param string $name The client's name
 * @param string $email The client's email
 * @return string The nicely formatted name and email
 */
function efpic_combine_name_email( $name, $email ) {
	$output = '';

	if ( ! empty( $name ) ) {
		$output .= $name;
	}
	
	if ( ! empty( $email ) ) {
		if ( ! empty( $name ) ) {
			$output .= ' ' . sprintf( __( '(%s)', 'efpic' ), $email );
		}
		else {
			$output .= $email;
		}
	}

	return $output;
}


/**
 * Return client name initials.
 *
 * @since 2.3.0
 *
 * @param string $name The client's name
 * @return string The initials in uppercase
 */
function efpic_get_client_initials( $name ) {
	$initials = substr( $name, 0, 2 );
	$initials = strtoupper( $initials );

	return $initials;
}


/**
 * Add new client to hashes.
 *
 * @since 2.3.0
 *
 * @param int $collection_id The collection post ID
 * @param string $name The client name
 * @param string $email The client email
 * @param array $args Additional data to save for the client
 * @return bool|string False or the new client's ident
 */
function efpic_add_client_to_hashes( $collection_id, $name = '', $email = '', $args = [] ) {
	// We need at least either name or email
	if ( empty( $name ) && empty( $email ) ) {
		return false;
	}

	// Get Existing hashes
	$collection_hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );
	if ( empty( $collection_hashes ) ) {
		$collection_hashes = [];
	}

	// Add new client
	$hash = substr( md5( rand() ), 0, 10 );
	$collection_hashes[$hash] = [
		'name' => $name,
		'email' => sanitize_email( $email ),
		'status' => 'sent',
		'time' => time(),
	];

	// Add additional data, eg. 'time' or 'status'
	if ( ! empty( $args ) ) {
		foreach( $args as $key => $arg ) {
			$collection_hashes[$hash][$key] = $arg;
		}
	}

	// Update meta
	update_post_meta( $collection_id, '_efpic_collection_hashes', $collection_hashes );

	return $hash;
}


/**
 * Update client selections, when collection images change.
 *
 * @since 2.3.0
 *
 * @param int $collection_id The collection post ID
 * @param string $gallery_ids The comma separated image IDs
 */
function efpic_update_client_selections( $collection_id, $gallery_ids ) {
	// Get clients
	$efpic_collection_hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );

	if ( empty( $efpic_collection_hashes ) ) {
		return;
	}
	
	$gallery_ids = explode( ',', $gallery_ids );

	// Iterate through clients
	foreach( $efpic_collection_hashes as $ident => $hash ) {
		$selection = get_post_meta( $collection_id, '_efpic_collection_selection_' . $ident, true );
		if ( empty( $selection['selection'] ) ) {
			continue;
		}

		foreach( $selection['selection'] as $image_id ) {
			// Delete selection, if the image is no longer there
			if ( ! in_array( $image_id, $gallery_ids ) ) {
				if ( ( $k = array_search( $image_id, $selection['selection'] ) ) !== false ) {
					unset( $selection['selection'][$k] );
				}

				// Delete markers for non-existing images
				if ( isset( $selection['markers']['id_' . $image_id] ) ) {
					unset( $selection['markers']['id_' . $image_id] );
				}
			}
		}

		update_post_meta( $collection_id, '_efpic_collection_selection_' . $ident, $selection );
	}
}


/**
 * Return the default client name.
 *
 * @since 2.3.0
 *
 * @return string The default client name.
 */
function efpic_get_default_client_name() {
	$name = apply_filters( 'efpic_default_client_name', __( 'Client', 'efpic' ) );

	return $name;
}


/**
 * Get collection link.
 *
 * @since 2.4.0
 * 
 * @param string $ident The ident parameter
 * @param int $collection_id The collection post ID
 * @return string The collection link
 */
function efpic_get_collection_link( $ident = '', $collection_id = null ) {
	return ( ! empty( $ident ) ) ? esc_url( add_query_arg( 'ident', $ident, get_the_permalink( $collection_id ) ) ) : get_the_permalink( $collection_id );
}


/**
 * Return a list of countries.
 *
 * @since 3.0.0
 *
 * @return array The list of countries.
 */
function efpic_get_countries() {
	$countries =[
		'af' => __( 'Afghanistan', 'efpic' ),
		'ax' => __( 'Åland Islands', 'efpic' ),
		'al' => __( 'Albania', 'efpic' ),
		'dz' => __( 'Algeria', 'efpic' ),
		'as' => __( 'American Samoa', 'efpic' ),
		'ad' => __( 'Andorra', 'efpic' ),
		'ao' => __( 'Angola', 'efpic' ),
		'ai' => __( 'Anguilla', 'efpic' ),
		'aq' => __( 'Antarctica', 'efpic' ),
		'ag' => __( 'Antigua and Barbuda', 'efpic' ),
		'ar' => __( 'Argentina', 'efpic' ),
		'am' => __( 'Armenia', 'efpic' ),
		'aw' => __( 'Aruba', 'efpic' ),
		'au' => __( 'Australia', 'efpic' ),
		'at' => __( 'Austria', 'efpic' ),
		'az' => __( 'Azerbaijan', 'efpic' ),
		'bs' => __( 'Bahamas', 'efpic' ),
		'bh' => __( 'Bahrain', 'efpic' ),
		'bd' => __( 'Bangladesh', 'efpic' ),
		'bb' => __( 'Barbados', 'efpic' ),
		'by' => __( 'Belarus', 'efpic' ),
		'be' => __( 'Belgium', 'efpic' ),
		'bz' => __( 'Belize', 'efpic' ),
		'bj' => __( 'Benin', 'efpic' ),
		'bm' => __( 'Bermuda', 'efpic' ),
		'bt' => __( 'Bhutan', 'efpic' ),
		'bo' => __( 'Bolivia', 'efpic' ),
		'bq' => __( 'Bonaire, Saint Eustatius and Saba', 'efpic' ),
		'ba' => __( 'Bosnia and Herzegovina', 'efpic' ),
		'bw' => __( 'Botswana', 'efpic' ),
		'bv' => __( 'Bouvet Island', 'efpic' ),
		'br' => __( 'Brazil', 'efpic' ),
		'io' => __( 'British Indian Ocean Territory', 'efpic' ),
		'bn' => __( 'Brunei', 'efpic' ),
		'bg' => __( 'Bulgaria', 'efpic' ),
		'bf' => __( 'Burkina Faso', 'efpic' ),
		'bi' => __( 'Burundi', 'efpic' ),
		'kh' => __( 'Cambodia', 'efpic' ),
		'cm' => __( 'Cameroon', 'efpic' ),
		'ca' => __( 'Canada', 'efpic' ),
		'cv' => __( 'Cape Verde', 'efpic' ),
		'ky' => __( 'Cayman Islands', 'efpic' ),
		'cf' => __( 'Central African Republic', 'efpic' ),
		'td' => __( 'Chad', 'efpic' ),
		'cl' => __( 'Chile', 'efpic' ),
		'cn' => __( 'China', 'efpic' ),
		'cx' => __( 'Christmas Island', 'efpic' ),
		'cc' => __( 'Cocos (Keeling) Islands', 'efpic' ),
		'co' => __( 'Colombia', 'efpic' ),
		'km' => __( 'Comoros', 'efpic' ),
		'cg' => __( 'Congo (Brazzaville)', 'efpic' ),
		'cd' => __( 'Congo (Kinshasa)', 'efpic' ),
		'ck' => __( 'Cook Islands', 'efpic' ),
		'cr' => __( 'Costa Rica', 'efpic' ),
		'hr' => __( 'Croatia', 'efpic' ),
		'cu' => __( 'Cuba', 'efpic' ),
		'cw' => __( 'Cura&ccedil;ao', 'efpic' ),
		'cy' => __( 'Cyprus', 'efpic' ),
		'cz' => __( 'Czech Republic', 'efpic' ),
		'dk' => __( 'Denmark', 'efpic' ),
		'dj' => __( 'Djibouti', 'efpic' ),
		'dm' => __( 'Dominica', 'efpic' ),
		'do' => __( 'Dominican Republic', 'efpic' ),
		'ec' => __( 'Ecuador', 'efpic' ),
		'eg' => __( 'Egypt', 'efpic' ),
		'sv' => __( 'El Salvador', 'efpic' ),
		'gq' => __( 'Equatorial Guinea', 'efpic' ),
		'er' => __( 'Eritrea', 'efpic' ),
		'ee' => __( 'Estonia', 'efpic' ),
		'sz' => __( 'Eswatini', 'efpic' ),
		'et' => __( 'Ethiopia', 'efpic' ),
		'fk' => __( 'Falkland Islands', 'efpic' ),
		'fo' => __( 'Faroe Islands', 'efpic' ),
		'fj' => __( 'Fiji', 'efpic' ),
		'fi' => __( 'Finland', 'efpic' ),
		'fr' => __( 'France', 'efpic' ),
		'gf' => __( 'French Guiana', 'efpic' ),
		'pf' => __( 'French Polynesia', 'efpic' ),
		'tf' => __( 'French Southern Territories', 'efpic' ),
		'ga' => __( 'Gabon', 'efpic' ),
		'gm' => __( 'Gambia', 'efpic' ),
		'ge' => __( 'Georgia', 'efpic' ),
		'de' => __( 'Germany', 'efpic' ),
		'gh' => __( 'Ghana', 'efpic' ),
		'gi' => __( 'Gibraltar', 'efpic' ),
		'gr' => __( 'Greece', 'efpic' ),
		'gl' => __( 'Greenland', 'efpic' ),
		'gd' => __( 'Grenada', 'efpic' ),
		'gp' => __( 'Guadeloupe', 'efpic' ),
		'gu' => __( 'Guam', 'efpic' ),
		'gt' => __( 'Guatemala', 'efpic' ),
		'gg' => __( 'Guernsey', 'efpic' ),
		'gn' => __( 'Guinea', 'efpic' ),
		'gw' => __( 'Guinea-Bissau', 'efpic' ),
		'gy' => __( 'Guyana', 'efpic' ),
		'ht' => __( 'Haiti', 'efpic' ),
		'hm' => __( 'Heard Island and McDonald Islands', 'efpic' ),
		'hn' => __( 'Honduras', 'efpic' ),
		'hk' => __( 'Hong Kong', 'efpic' ),
		'hu' => __( 'Hungary', 'efpic' ),
		'is' => __( 'Iceland', 'efpic' ),
		'in' => __( 'India', 'efpic' ),
		'id' => __( 'Indonesia', 'efpic' ),
		'ir' => __( 'Iran', 'efpic' ),
		'iq' => __( 'Iraq', 'efpic' ),
		'ie' => __( 'Ireland', 'efpic' ),
		'im' => __( 'Isle of Man', 'efpic' ),
		'il' => __( 'Israel', 'efpic' ),
		'it' => __( 'Italy', 'efpic' ),
		'ci' => __( 'Ivory Coast', 'efpic' ),
		'jm' => __( 'Jamaica', 'efpic' ),
		'jp' => __( 'Japan', 'efpic' ),
		'je' => __( 'Jersey', 'efpic' ),
		'jo' => __( 'Jordan', 'efpic' ),
		'kz' => __( 'Kazakhstan', 'efpic' ),
		'ke' => __( 'Kenya', 'efpic' ),
		'ki' => __( 'Kiribati', 'efpic' ),
		'kw' => __( 'Kuwait', 'efpic' ),
		'kg' => __( 'Kyrgyzstan', 'efpic' ),
		'la' => __( 'Laos', 'efpic' ),
		'lv' => __( 'Latvia', 'efpic' ),
		'lb' => __( 'Lebanon', 'efpic' ),
		'ls' => __( 'Lesotho', 'efpic' ),
		'lr' => __( 'Liberia', 'efpic' ),
		'ly' => __( 'Libya', 'efpic' ),
		'li' => __( 'Liechtenstein', 'efpic' ),
		'lt' => __( 'Lithuania', 'efpic' ),
		'lu' => __( 'Luxembourg', 'efpic' ),
		'mo' => __( 'Macao', 'efpic' ),
		'mg' => __( 'Madagascar', 'efpic' ),
		'mw' => __( 'Malawi', 'efpic' ),
		'my' => __( 'Malaysia', 'efpic' ),
		'mv' => __( 'Maldives', 'efpic' ),
		'ml' => __( 'Mali', 'efpic' ),
		'mt' => __( 'Malta', 'efpic' ),
		'mh' => __( 'Marshall Islands', 'efpic' ),
		'mq' => __( 'Martinique', 'efpic' ),
		'mr' => __( 'Mauritania', 'efpic' ),
		'mu' => __( 'Mauritius', 'efpic' ),
		'yt' => __( 'Mayotte', 'efpic' ),
		'mx' => __( 'Mexico', 'efpic' ),
		'fm' => __( 'Micronesia', 'efpic' ),
		'md' => __( 'Moldova', 'efpic' ),
		'mc' => __( 'Monaco', 'efpic' ),
		'mn' => __( 'Mongolia', 'efpic' ),
		'me' => __( 'Montenegro', 'efpic' ),
		'ms' => __( 'Montserrat', 'efpic' ),
		'ma' => __( 'Morocco', 'efpic' ),
		'mz' => __( 'Mozambique', 'efpic' ),
		'mm' => __( 'Myanmar', 'efpic' ),
		'na' => __( 'Namibia', 'efpic' ),
		'nr' => __( 'Nauru', 'efpic' ),
		'np' => __( 'Nepal', 'efpic' ),
		'nl' => __( 'Netherlands', 'efpic' ),
		'nc' => __( 'New Caledonia', 'efpic' ),
		'nz' => __( 'New Zealand', 'efpic' ),
		'ni' => __( 'Nicaragua', 'efpic' ),
		'ne' => __( 'Niger', 'efpic' ),
		'ng' => __( 'Nigeria', 'efpic' ),
		'nu' => __( 'Niue', 'efpic' ),
		'nf' => __( 'Norfolk Island', 'efpic' ),
		'mk' => __( 'North Macedonia', 'efpic' ),
		'mp' => __( 'Northern Mariana Islands', 'efpic' ),
		'kp' => __( 'North Korea', 'efpic' ),
		'no' => __( 'Norway', 'efpic' ),
		'om' => __( 'Oman', 'efpic' ),
		'pk' => __( 'Pakistan', 'efpic' ),
		'ps' => __( 'Palestinian Territory', 'efpic' ),
		'pa' => __( 'Panama', 'efpic' ),
		'pg' => __( 'Papua New Guinea', 'efpic' ),
		'py' => __( 'Paraguay', 'efpic' ),
		'pe' => __( 'Peru', 'efpic' ),
		'ph' => __( 'Philippines', 'efpic' ),
		'pn' => __( 'Pitcairn', 'efpic' ),
		'pl' => __( 'Poland', 'efpic' ),
		'pt' => __( 'Portugal', 'efpic' ),
		'pr' => __( 'Puerto Rico', 'efpic' ),
		'qa' => __( 'Qatar', 'efpic' ),
		're' => __( 'Reunion', 'efpic' ),
		'ro' => __( 'Romania', 'efpic' ),
		'ru' => __( 'Russia', 'efpic' ),
		'rw' => __( 'Rwanda', 'efpic' ),
		'bl' => __( 'Saint Barth&eacute;lemy', 'efpic' ),
		'sh' => __( 'Saint Helena', 'efpic' ),
		'kn' => __( 'Saint Kitts and Nevis', 'efpic' ),
		'lc' => __( 'Saint Lucia', 'efpic' ),
		'mf' => __( 'Saint Martin (French part)', 'efpic' ),
		'sx' => __( 'Saint Martin (Dutch part)', 'efpic' ),
		'pm' => __( 'Saint Pierre and Miquelon', 'efpic' ),
		'vc' => __( 'Saint Vincent and the Grenadines', 'efpic' ),
		'ws' => __( 'Samoa', 'efpic' ),
		'sm' => __( 'San Marino', 'efpic' ),
		'st' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'efpic' ),
		'sa' => __( 'Saudi Arabia', 'efpic' ),
		'sn' => __( 'Senegal', 'efpic' ),
		'rs' => __( 'Serbia', 'efpic' ),
		'sc' => __( 'Seychelles', 'efpic' ),
		'sl' => __( 'Sierra Leone', 'efpic' ),
		'sg' => __( 'Singapore', 'efpic' ),
		'sk' => __( 'Slovakia', 'efpic' ),
		'si' => __( 'Slovenia', 'efpic' ),
		'sb' => __( 'Solomon Islands', 'efpic' ),
		'so' => __( 'Somalia', 'efpic' ),
		'za' => __( 'South Africa', 'efpic' ),
		'gs' => __( 'South Georgia/Sandwich Islands', 'efpic' ),
		'kr' => __( 'South Korea', 'efpic' ),
		'ss' => __( 'South Sudan', 'efpic' ),
		'es' => __( 'Spain', 'efpic' ),
		'lk' => __( 'Sri Lanka', 'efpic' ),
		'sd' => __( 'Sudan', 'efpic' ),
		'sr' => __( 'Suriname', 'efpic' ),
		'sj' => __( 'Svalbard and Jan Mayen', 'efpic' ),
		'se' => __( 'Sweden', 'efpic' ),
		'ch' => __( 'Switzerland', 'efpic' ),
		'sy' => __( 'Syria', 'efpic' ),
		'tw' => __( 'Taiwan', 'efpic' ),
		'tj' => __( 'Tajikistan', 'efpic' ),
		'tz' => __( 'Tanzania', 'efpic' ),
		'th' => __( 'Thailand', 'efpic' ),
		'tl' => __( 'Timor-Leste', 'efpic' ),
		'tg' => __( 'Togo', 'efpic' ),
		'tk' => __( 'Tokelau', 'efpic' ),
		'to' => __( 'Tonga', 'efpic' ),
		'tt' => __( 'Trinidad and Tobago', 'efpic' ),
		'tn' => __( 'Tunisia', 'efpic' ),
		'tr' => __( 'Turkey', 'efpic' ),
		'tm' => __( 'Turkmenistan', 'efpic' ),
		'tc' => __( 'Turks and Caicos Islands', 'efpic' ),
		'tv' => __( 'Tuvalu', 'efpic' ),
		'ug' => __( 'Uganda', 'efpic' ),
		'ua' => __( 'Ukraine', 'efpic' ),
		'ae' => __( 'United Arab Emirates', 'efpic' ),
		'gb' => __( 'United Kingdom (UK)', 'efpic' ),
		'us' => __( 'United States (US)', 'efpic' ),
		'um' => __( 'United States (US) Minor Outlying Islands', 'efpic' ),
		'uy' => __( 'Uruguay', 'efpic' ),
		'uz' => __( 'Uzbekistan', 'efpic' ),
		'vu' => __( 'Vanuatu', 'efpic' ),
		'va' => __( 'Vatican', 'efpic' ),
		've' => __( 'Venezuela', 'efpic' ),
		'vn' => __( 'Vietnam', 'efpic' ),
		'vg' => __( 'Virgin Islands (British)', 'efpic' ),
		'vi' => __( 'Virgin Islands (US)', 'efpic' ),
		'wf' => __( 'Wallis and Futuna', 'efpic' ),
		'eh' => __( 'Western Sahara', 'efpic' ),
		'ye' => __( 'Yemen', 'efpic' ),
		'zm' => __( 'Zambia', 'efpic' ),
		'zw' => __( 'Zimbabwe', 'efpic' )
	];

	natsort( $countries );

	$countries = apply_filters( 'efpic_countries', $countries );

	return $countries;
}


/**
 * Get country name by country code.
 *
 * @since 3.0.0
 *
 * @param string $code The country code.
 * @return string The country name or an empty string.
 */
function efpic_get_country_name( $code ) {
	$country = '';
	$countries = efpic_get_countries();
	if ( ! empty( $countries[$code] ) ) {
		$country = $countries[$code];
	}

	$country = apply_filters( 'efpic_country', $country, $code );

	return $country;
}


/**
 * Render country select dropdown.
 *
 * @since 3.0.0
 *
 * @param string $selected Currently selected country code.
 * @param array $args Additional arguments (name, id, class).
 * @return string The country select menu HTML.
 */
function efpic_country_select( $selected = '', $args = [] ) {
	$defaults = [
		'name' => 'country',
		'id' => 'country',
		'class' => 'efpic-country-select',
	];

	$args = wp_parse_args( $args, $defaults );

	$output = sprintf(
		'<select name="%s" id="%s" class="%s">',
		esc_attr( $args['name'] ),
		esc_attr( $args['id'] ),
		esc_attr( $args['class'] )
	);

	$output .= sprintf(
		'<option value="">%s</option>',
		esc_html__( 'Select a country', 'efpic' )
	);

	foreach ( efpic_get_countries() as $code => $name ) {
		$output .= sprintf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $code ),
			selected( $selected, $code, false ),
			esc_html( $name )
		);
	}

	$output .= '</select>';

	return $output;
}
