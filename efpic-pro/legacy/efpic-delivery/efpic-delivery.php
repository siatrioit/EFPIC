<?php
/**
 * Delivery
 *
 * Deliver final images to your clients
 *
 * @since delivery (0.0.1)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Register custom post statuses for delivery.
 *
 * @since delivery (1.0.0)
 */
function efpic_delivery_collection_post_status() {
	register_post_status( 'delivery-draft', array(
		'label' => _x( 'Delivery Draft', 'post status name', 'efpic-pro' ),
		'public' => true,
		'exclude_from_search' => false,
		'show_in_admin_all_list' => true,
		'show_in_admin_status_list' => true,
		'label_count' => _n_noop( 'Delivery Draft <span class="count">(%s)</span>', 'Delivery Draft <span class="count">(%s)</span>', 'efpic-pro' ),
	) );

	register_post_status( 'delivered', array(
		'label' => _x( 'Delivered', 'post status name', 'efpic-pro' ),
		'public' => true,
		'exclude_from_search' => false,
		'show_in_admin_all_list' => true,
		'show_in_admin_status_list' => true,
		'label_count' => _n_noop( 'Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'efpic-pro' ),
	) );
}

add_action( 'init', 'efpic_delivery_collection_post_status' );


/**
 * Localize strings.
 *
 * @since delivery (1.0.0)
 *
 * @see efpic/efpic.php
 */
function efpic_delivery_localization_strings( $strings ) {
	$strings['delivered_option_label'] = __( 'Delivered', 'efpic-pro' );
	$strings['delivery_draft_option_label'] = __( 'Delivery Draft', 'efpic-pro' );
	return $strings;
}

add_filter( 'efpic_localization_strings', 'efpic_delivery_localization_strings' );


/**
 * Add custom post status to quick edit.
 *
 * @since delivery (1.0.0)
 */
function efpic_delivery_scripts() {
	$current_screen = get_current_screen();

	if ( empty( $current_screen ) ) {
		return;
	}

	// Only load those styles on edit collection screens
	if ( 'edit-efpic_collection' == $current_screen->id ) {
		wp_enqueue_script( 'efpic-delivery-admin', EFPIC_PRO_URL . 'legacy/efpic-delivery/js/efpic-delivery.js', array( 'jquery' ), filemtime( EFPIC_PRO_PATH . 'legacy/efpic-delivery/js/efpic-delivery.js' ), true );
	}
}

add_action( 'admin_enqueue_scripts', 'efpic_delivery_scripts', 11 );


/**
 * Add background image to collection.
 * 
 * @since delivery (0.1.0)
 * 
 * @see efpic/frontend/efpic-app.php
 */
function efpic_delivery_collection_background( $custom_styles ) {

	$post = get_post();

	$delivery_option = get_post_meta( $post->ID, '_efpic_collection_delivery_option', true );
	if ( $delivery_option == 'external' ) {
		return $custom_styles;
	}

	if ( 'delivery-draft' == $post->post_status OR 'delivered' == $post->post_status ) {

		$delivery_images = get_post_meta( $post->ID, '_efpic_collection_delivery_ids', true );

		$image_ids = explode( ',', $delivery_images );
		shuffle( $image_ids );
		$image = wp_get_attachment_image_src( $image_ids[0], 'efpic_large' );

		if ( $image ) {
			$custom_styles .= 'body { background: url(' . $image[0] . ') fixed; background-size: cover; }';
		}

	}

	return $custom_styles;
}

// add_filter( 'efpic_custom_styles', 'efpic_delivery_collection_background' );


/**
 * Add "Delivery Final Images" button to publishing meta box.
 *
 * @since delivery (0.1.0)
 *
 * @param object $post The collection post object
 */
function efpic_delvery_add_delivery_button( $post ) {
	if ( in_array( $post->post_status, [ 'approved', 'expired' ] ) ) {
		ob_start();

		?>
		<a class="button button-primary js-efpic-deliver" href="<?php print wp_nonce_url( admin_url( "post.php?post=" . $post->ID ), -1, 'delivery' ); ?>"><?php _e( 'Deliver Final Images', 'efpic-pro' ); ?></a>
		<?php

		echo ob_get_clean();
	}
}

add_action( 'efpic_after_major_publishing_actions', 'efpic_delvery_add_delivery_button' );


/**
 * Generate output for delivery-draft collection status.
 *
 * @since 1.4.0
 *
 * @param object $post The collection post object.
 * @return string The collection status.
 */
function efpic_collection_status_delivery_draft( $post ) {
	$post_status = $post->post_status;
	if ( ! in_array( $post_status, [ 'delivery-draft' ] ) ) {
		return;
	}

	$status = '<span class="status status--draft">';
	$status .= __( 'Preparing delivery', 'efpic' );
	$status .= '</span>';

	return $status;
}


/**
 * Generate output for delivered collection status.
 *
 * @since delivery (0.1.0)
 * @since 1.4.0 delivery-draft now has its own function
 *
 * @param object $post The collection post object.
 * @return string The collection status.
 */
function efpic_collection_status_delivered( $post ) {
	$post_status = $post->post_status;
	if ( ! in_array( $post_status, [ 'delivered' ] ) ) {
		return;
	}

	$status = '<span class="status status--delivered">';
	$status .= __( 'Delivered', 'efpic' );
	$status .= '</span>';

	$status_meta_output = efpic_collection_status_meta( $post->ID );

	return $status . $status_meta_output;
}


/**
 * Add delivered status meta.
 *
 * @since 1.4.0
 * 
 * @param array $status_meta The status meta.
 * @param int $collection_id The collection post ID.
 * @return array The filtered status meta.
 */
function efpic_delivery_status_meta( $status_meta, $collection_id ) {
	$post_status = get_post_status( $collection_id );

	// Delivered time
	$delivered_time = efpic_get_collection_history_event_time( $collection_id, 'delivered' );

	if ( $delivered_time != false && in_array( $post_status, [ 'delivered' ] ) ) {
		$status_meta[] = [
			'label' => __( 'Delivered', 'efpic' ),
			'data' => wp_date( get_option( 'date_format' ), $delivered_time ) . ', ' . wp_date( get_option( 'time_format' ), $delivered_time )
		];
	}

	return $status_meta;
}

add_filter( 'efpic_status_meta', 'efpic_delivery_status_meta', 10, 2 );


/**
 * Add custom collection status to meta box.
 *
 * @since delivery (0.1.0)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 *
 * @param string $status The collection status
 * @return string The filtered status
 */
function efpic_delivery_add_collection_status( $status ) {
	$status['delivery_draft'] = 140;
	$status['delivered'] = 150;
	return $status;
}

add_filter( 'efpic_collection_status', 'efpic_delivery_add_collection_status', 10, 2 );


/**
 * Create zip file from collection images.
 *
 * The file will only be created when actually sending/publishing the collection.
 *
 * @since delivery (0.0.1)
 */
function efpic_delivery_create_download_zip() {
	$post = get_post();
	$post_id = $post->ID;
	
	if ( ! class_exists( 'ZipArchive' ) ) {
		return false;
	}

	$efpic_delivery_ids = get_post_meta( $post_id, '_efpic_collection_delivery_ids', true );

	// Check if a zip file with the current images already exists
	$args = [
		'post_parent' => $post_id,
		'post_type' => 'attachment',
		'post_mime_type' => 'application/zip',
		'meta_query' => [
			[
				'key' => '_efpic_zip_content',
				'value' => $efpic_delivery_ids
			]
		],
		'fields' => 'ids'
	];

	$existing_zips = get_posts( $args );

	// If there is one already, no need to create a new zip, just return the existing URL
	if ( ! empty( $existing_zips[0] ) ) {
		return wp_get_attachment_url( $existing_zips[0] );
	}

	// Only create zip, if there are images, duh!
	if ( ! empty( $efpic_delivery_ids ) ) {

		// Check if a zip file for this collection already exists. If so, delete it.
		// We need to remove our pre_get_posts filter first, to actually get collection attachments :-)
		remove_action( 'pre_get_posts', 'efpic_exclude_collection_images_from_library', 999 );
		$existing_zip_files = get_attached_media( 'application/zip', $post_id );
		add_action( 'pre_get_posts', 'efpic_exclude_collection_images_from_library', 999 );

		// Delete the respecive previous version of a zip file before creating a new one
		// Delete the zip file containing selected images
		if ( is_array( $existing_zip_files ) ) {
			foreach( $existing_zip_files as $zip_file ) {
				if ( $zip_file->post_title == sanitize_file_name( get_the_title( $post_id ) ) ) {
					wp_delete_attachment( $zip_file->ID, true );
				}
			}
		}

		// Let's create a fresh zip
		$zip = new ZipArchive();

		// Get path (where collection images are saved)
		$upload_dir = wp_upload_dir();
		$dir = $upload_dir['basedir'] . '/efpic/collections/' . $post_id . '/delivery/';

		$filename =  $dir . sanitize_file_name( get_the_title( $post_id ) ) . '.zip';

		$delivery_zipurl = $upload_dir['baseurl'] . '/efpic/collections/' . $post_id . '/delivery/' . sanitize_file_name( get_the_title( $post_id ) ) . '.zip';

		if ( $zip->open( $filename, ZipArchive::CREATE ) !== true ) {
			exit( "Cannot open <$filename>\n" );
		}

		// Make array of image ids
		$images = explode( ',', $efpic_delivery_ids );

		// Loop through all images
		foreach( $images as $image ) {
			$file = basename( get_attached_file( $image ) );
			// Add image to the zip file
			$zip->addFile( get_attached_file( $image ), $file );
		}

		// Close the zip file
		$zip->close();

		// Add zip file to media library
		$filetype = wp_check_filetype( basename( $filename ), null );

		$attachment = array(
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit',
			'post_mime_type' => $filetype['type']
		);

		$attachment_id = wp_insert_attachment( $attachment, $filename, $post_id );

		// Add the image IDs as post meta, so we can compare changes later
		update_post_meta( $attachment_id, '_efpic_zip_content', $efpic_delivery_ids );

		return $delivery_zipurl;
	}

	return false;
}


/**
 * Sanitize & save Delivery collection settings.
 *
 * @since delivery (Unknown)
 *
 * @param int $post_id The collection post ID
 */
function efpic_delivery_save_collection( $post_id ) {
	// Abort if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	// Check if nonce is set
	if ( ! isset( $_POST['efpic_delivery_ids_nonce'] ) )
		return $post_id;

	// Verify that the nonce is valid
	if ( ! wp_verify_nonce( $_POST['efpic_delivery_ids_nonce'], 'efpic_delivery_ids' ) )
		return $post_id;

	// Check user permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	
	// Check if this is a delivery collection
	if ( empty( $_POST['efpic_delivery_option'] ) ) {
		return $post_id;
	}

	// If there are images, zip 'em
	if ( ! empty( $_POST['delivery_image_ids'] ) AND $_POST['efpic_delivery_option'] == 'upload' ) {
		$url = efpic_delivery_create_download_zip();
		$temp['url'] = esc_url_raw( $url );
		update_post_meta( $post_id, '_efpic_collection_delivery_ids', $_POST['delivery_image_ids'] );
		update_post_meta( $post_id, '_efpic_collection_delivery_option', 'upload' );
		update_post_meta( $post_id, '_efpic_collection_delivery_url', $temp );
	}
	elseif ( $_POST['efpic_delivery_option'] == 'external' AND ! empty( $_POST['efpic_delivery_external_url'] ) ) {
		$temp['url'] = esc_url_raw( $_POST['efpic_delivery_external_url'] );
		update_post_meta( $post_id, '_efpic_collection_delivery_option', 'external' );
		update_post_meta( $post_id, '_efpic_collection_delivery_url', $temp );
	}
	else {
		delete_post_meta( $post_id, '_efpic_collection_delivery_option' );
		delete_post_meta( $post_id, '_efpic_collection_delivery_url' );
	}

	if ( $_POST['efpic_collection_share_method'] == 'efpic-send-email' ) {

		update_post_meta( $post_id, '_efpic_collection_share_method', 'efpic-send-email' );

		// Check if multiple clients
		$email_addresses = $_POST['efpic_delivery_email_address'];
		$email_addresses = explode( ', ', $email_addresses );

		if ( is_array( $email_addresses ) AND count( $email_addresses ) > 1 ) {
	
			$email_addresses = array_filter( $email_addresses, 'efpic_validate_email_address' );

			// Make a string to save them
			$email_addresses = implode( ', ', $email_addresses );

			update_post_meta( $post_id, '_efpic_delivery_email_address', $email_addresses );

		}
		// Make sure we have one valid email address
		elseif ( is_email ( $_POST['efpic_delivery_email_address'] ) ) {

			$efpic_collection_email_address = sanitize_email( $_POST['efpic_delivery_email_address'] );
			// Update the email address in the database
			update_post_meta( $post_id, '_efpic_delivery_email_address', $efpic_collection_email_address );
		}
		else {
			delete_post_meta( $post_id, '_efpic_delivery_email_address' );
		}

		// Clean up the collection description
		$efpic_delivery_description = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['efpic_delivery_description'] ) ) );

		if ( ! empty( $efpic_delivery_description ) ) {
			update_post_meta( $post_id, '_efpic_delivery_description', $efpic_delivery_description );
		}
		else {
			delete_post_meta( $post_id, '_efpic_delivery_description' );
		}
	}
	elseif ( $_POST['efpic_collection_share_method'] == 'efpic-copy-link' ) {
		update_post_meta( $post_id, '_efpic_collection_share_method', 'efpic-copy-link' );
	}
	else {
		delete_post_meta( $post_id, '_efpic_collection_share_method' );
	}

}

add_action( 'save_post_efpic_collection', 'efpic_delivery_save_collection', 9 );


/**
 * Replace "Edit Collection" with a custom title.
 *
 * @since delivery (1.0.0)
 *
 * @global object $post The collection post object
 * @global string $title The collection title
 * @global string $action The current action
 */
function efpic_delivery_replace_edit_screen_title() {
	global $post, $title, $action;

	if ( empty( $post ) OR empty( $action ) ) {
		return;
	}

	if ( 'edit' == $action AND 'efpic_collection' == $post->post_type AND 'delivered' == $post->post_status ) {
		if ( empty( $post->post_title ) ) {
			$title = __( '(no title)', 'efpic-pro' );
		} else {
			$title = $post->post_title;
		}
	}

	if ( 'edit' == $action AND 'efpic_collection' == $post->post_type AND 'delivery-draft' == $post->post_status ) {
		$title = __( 'Edit Delivery', 'efpic-pro' );
	}
}

add_action( 'admin_head', 'efpic_delivery_replace_edit_screen_title' );


/**
 * Add the skip link after new collection title.
 * 
 * @since delivery (1.0.0)
 */
function efpic_delivery_add_skip_link() {
	$current_screen = get_current_screen();

	if ( ! empty( $current_screen->post_type ) AND $current_screen->post_type == 'efpic_collection' AND ! empty( $current_screen->action ) AND $current_screen->action == 'add' ) {
		echo '<a class="efpic-skip-to-delivery" href="' . wp_nonce_url( admin_url( "post.php" ), -1, 'delivery' ) . '">' . __( 'Skip to Delivery', 'efpic-pro' ). '</a>';
	}
}

add_action( 'in_admin_footer', 'efpic_delivery_add_skip_link' );


/**
 * Add Delivery data to the appstate.
 *
 * @since delivery (1.0.0)
 *
 * @see efpic/frontend/includes/efpic-template-functions.php
 *
 * @param array $state The collection app state
 * @return array the filtered app state
 */
add_filter( 'efpic_app_state', function( $state ) { 
	global $post;

	if ( $post->post_status == 'delivery-draft' OR $post->post_status == 'delivered' ) {
		$delivery_data = get_post_meta( $post->ID, '_efpic_collection_delivery_url', true );

		if ( ! empty( $delivery_data['url'] ) ) {
			$state['delivery_zip_url'] = $delivery_data['url'];
		}

		$delivery_option = get_post_meta( $post->ID, '_efpic_collection_delivery_option', true );

		if ( $delivery_option == 'upload' ) {
			$state['delivery_option'] = 'upload';
		}
		elseif ( $delivery_option == 'external' ) {
			$state['delivery_option'] = 'external';
		}

		/* translators: Button text, single image download button */
		$state['i18n_delivery_single_download_button_label'] = __( 'Download', 'efpic-pro' );
	}

	return $state;
} );


/**
 * Change default image title for delivery collections.
 * 
 * @since delivery (1.0.0)
 *
 * @see efpic/frontend/includes/efpic-template-functions.php
 *
 * @param array $current_image The current image
 * @param object $post The collection post object
 * @return array The filtered image
 */
function efpic_delivery_image_title( $current_image, $post ) {
	
	if ( $post->post_status == 'delivery-draft' OR $post->post_status == 'delivered' ) {
		$image_meta = wp_get_attachment_metadata( $current_image['imageID'] );
		$title = strtok( basename( $image_meta['file'] ), '?' );
		unset( $current_image['title'] );
		$current_image['title']['name'] = $title;
	}
	
	return $current_image;
}

add_filter( 'efpic_single_image_data', 'efpic_delivery_image_title', 11, 2 );


/**
 * Download tracking.
 * 
 * @since delivery (1.0.0)
 */
function efpic_track_download() {
	// Nonce check!
	if ( ! check_ajax_referer( 'efpic-ajax-security', 'security', false ) ) {
		$return = array(
			/* Uses efpic core text domain, will be translated over there  */
			'message' => __( '<strong>Error:</strong> Nonce check failed.<br />Refresh your browser window.', 'efpic-pro' ),
			'button_text' => __( 'OK', 'efpic-pro' )
		);
		wp_send_json_error( $return );
		exit;
	}

	// Sanitize and validate post id
	$post_id = sanitize_key( $_POST['postid'] );

	// Does this collection exist?
	if ( ! is_string( get_post_status( $post_id ) ) ) {
		$return = array(
			/* Uses efpic core text domain, will be translated over there  */
			'message' => __( 'Error: Post id is not set.', 'efpic' ),
			'button_text' => __( 'OK', 'efpic' )
		);
		wp_send_json_error( $return );
		exit;
	}

	if ( get_post_status( $_POST['postid'] ) != 'delivery-draft' AND get_post_status( $_POST['postid'] ) != 'delivered' ) {
		$return = array(
			/* Uses efpic core text domain, will be translated over there  */
			'message' => __( 'Not a delivery collection.', 'efpic-pro' ),
			'button_text' => __( 'OK', 'efpic-pro' )
		);
		wp_send_json_error( $return );
		exit;
	}

	// Do not track if it is the collection author
	if ( get_current_user_id() == get_post_field( 'post_author', $_POST['postid'] ) ) {
		$return = array(
			'message' => __( 'Collection author will not be tracked.', 'efpic-pro' ),
			'button_text' => __( 'OK', 'efpic-pro' )
		);
		wp_send_json_success( $return );
		exit;
	}

	// Save tracking data in db
	$tracking_data = get_post_meta( $_POST['postid'], '_efpic_delivery_download_tracking', true );

	if ( ! is_array( $tracking_data ) ) {
		$tracking_data = array();
	}
	// Track: timestamp, all or single?
	if ( $_POST['download'] == 'zip' ) {
		$tracking_data[] = array(
			'time' => time(),
			'download' => 'zip',
		);
	}

	if ( $_POST['download'] == 'image' ) {
		$tracking_data[] = array(
			'time' => time(),
			'download' => $_POST['imageid'],
		);
	}

	$tracking = update_post_meta( $_POST['postid'], '_efpic_delivery_download_tracking', $tracking_data );

	// It worked
	if ( $tracking ) {
			$return = array(
				'message' => __( 'Success', 'efpic-pro' ),
				'button_text' => __( 'OK', 'efpic-pro' )
			);
			wp_send_json_success( $return );

	}
	// It didn't work
	else {
		// Return error message
		$return = array(
			'message' => __( 'Error', 'efpic-pro' ),
			'button_text' => __( 'OK', 'efpic-pro' )
		);
		wp_send_json_error( $return );
	}

	exit;
}

add_action( 'wp_ajax_efpic_track_download', 'efpic_track_download' );
add_action( 'wp_ajax_nopriv_efpic_track_download', 'efpic_track_download' );


/**
 * Add delivery backbone template.
 *
 * @since delivery (1.0.0)
 *
 * @see efpic/frontend/efpic-app.php
 * 
 * @param array $templates All the templates used in the front end
 * @return array The filtered templates
 */
function efpic_delviery_add_delivery_template( $templates ) {
	$templates['delivery'] = EFPIC_PRO_PATH . 'legacy/efpic-delivery/js/templates/delivery.php';
	return $templates;
}

add_filter( 'efpic_load_backbone_templates', 'efpic_delviery_add_delivery_template' );


/**
 * Add Delivery view.
 *
 * @since delivery (1.0.0)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param array $cmv Collections, models and views
 * @return array The filtered collections, models and views
 */
function efpic_delivery_add_delivery_view( $cmv ) {
	$cmv['delivery-view'] = EFPIC_PRO_URL . 'legacy/efpic-delivery/js/views/delivery-view.js?v=' . filemtime( EFPIC_PRO_PATH . 'legacy/efpic-delivery/js/views/delivery-view.js' );

	return $cmv;
}

add_filter( 'efpic_load_cmv', 'efpic_delivery_add_delivery_view' );


/**
 * Add custom edit screens for our custom post statuses.
 *
 * @since delivery (1.0.0)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 *
 * @param array $post_statuses The collection status
 */
function efpic_delivery_edit_screen_post_status( $post_statuses ) {
	$post_statuses['delivered'] = 'efpic_display_delivered_view';
	$post_statuses['delivery-draft'] = 'efpic_display_delivery_draft_view';

	return $post_statuses;
}

add_filter( 'efpic_edit_screen_post_status', 'efpic_delivery_edit_screen_post_status' );


/**
 * Display delivery-draft view on the collection edit screen.
 *
 * @since delivery (1.0.0)
 *
 * @param object $post The collection post object
 */
function efpic_display_delivery_draft_view( $post ) {
	// Load the IDs of all uploaded images into an array
	$delivery_data = get_post_meta( $post->ID, '_efpic_collection_delivery_ids', true );
	if ( ! empty( $delivery_data ) ) {
		$delivery_image_ids = explode( ',', $delivery_data );
		$delivery_image_count = count( $delivery_image_ids );
	}
	else {
		$delivery_image_ids = '';
		$delivery_image_count = 0;
	}

	$delivery_gallery_class = '';
	if ( ! empty( $delivery_data ) ) {
		$delivery_gallery_class = ' efpic-gallery-has-images';
	}
	if ( 10 < $delivery_image_count ) {
		$delivery_gallery_class .= ' is-collapsible js-collapsed';
	}

	ob_start();
?>
	<div class="postbox efpic-postbox efpic-delivery-postbox <?php echo $delivery_gallery_class; ?>">
		<div class="efpic-postbox-inner">
			<h2><span class="stepcounter">1</span> <?php _e( 'Deliver Final Images', 'efpic-pro' ); ?></h2>

			<?php
			$delivery_option = get_post_meta( $post->ID, '_efpic_collection_delivery_option', true );
			if ( empty( $delivery_option ) ) {
				$delivery_option = 'upload';
			}

			$delivery_images = get_post_meta( $post->ID, '_efpic_collection_delivery_url', true );
			$external_url = '';
			if ( ! empty( $delivery_images ) ) {
				$external_url = $delivery_images['url'];
			}

			?>
			<input class="efpic-radio-tab-input" type="radio" name="efpic_delivery_option" value="upload" id="delivery-option-upload" <?php checked( $delivery_option, 'upload' ); ?> />
			<input class="efpic-radio-tab-input" type="radio" name="efpic_delivery_option" value="external" id="delivery-option-external" <?php checked( $delivery_option, 'external' ); ?> />

			<div class="efpic-radio-tabs">
				<label class="efpic-radio-tab" for="delivery-option-upload" tabindex="0"><?php _e( 'Upload images', 'efpic-pro' ); ?></label>
				<label class="efpic-radio-tab" for="delivery-option-external" tabindex="0"><?php _e( 'Use external URL', 'efpic-pro' ); ?></label>
			</div><!-- .efpic-radio-tab -->

			<?php
			echo '<div class="efpic-gallery-thumbnails">';
			// Define the drag & drop zone for our uploader
			if ( ! empty( $delivery_image_ids ) ) {

				// Loop through all uploaded images
				foreach ( $delivery_image_ids as $delivery_image_id ) {

					// Load filename
					$img_name = wp_get_attachment_image_src( $delivery_image_id, 'full' );

					// Define the attributes to output with our thumbnails
					$attr = array(
						'title' => basename( $img_name[0] ),
						'draggable' => 'false'
					);

					// Construct the image markup
					echo '<figure>';
					echo '<div class="efpic-gallery-thumbnail-box">';
					echo '<div class="efpic-gallery-thumbnail-box-inner">';
					echo wp_get_attachment_image( $delivery_image_id, 'efpic-small', 0, $attr );
					echo '</div></div></figure>';
				}
			}
			echo '</div><!-- .efpic-gallery-thumbnails -->';
			?>

			<div class="toggle-efpic-gallery-height">
				<a href="#" class="js-toggle-efpic-gallery-height"><span class="show"><?php _e( 'Show all images', 'efpic-pro' ); ?></span><span class="hide"><?php _e( 'Hide images', 'efpic-pro' ); ?></span></a>
			</div>

			<?php
			// Add separate filter for delivery upload
			$efpic_before_delivery_upload = '';
			$efpic_before_delivery_upload = apply_filters( 'efpic_before_delivery_upload', $efpic_before_delivery_upload );
			echo $efpic_before_delivery_upload;
			?>

			<div class="efpic-gallery-uploader delivery-upload">
				<input type="text" id="delivery_image_ids" name="delivery_image_ids" value="<?php echo $delivery_data; ?>" autocomplete="off" style="display: none;" />
				<?php wp_nonce_field( 'efpic_delivery_ids', 'efpic_delivery_ids_nonce' ); ?>
				<p class="efpic-drag-info"><?php _e( 'Drag and drop your images here or click the button to upload', 'efpic-pro' ); ?></p>
				<p><a class="button efpic-upload-delivery-button" href="#"><?php _e( 'Upload / Edit Images', 'efpic-pro' ); ?></a></p>
				<p class="efpic-max-file-size"><?php echo __( 'Maximum upload size', 'efpic-pro' ) . ': ' . size_format( wp_max_upload_size() ); ?> <a class="efpic-help" href="https://efpic.io/docs/faq#maximum-upload-size" target="_blank"><?php _e( 'Help', 'efpic-pro' ); ?></a></p>
			</div><!-- .efpic-gallery-uploader -->
			<div class="efpic-delivery-zip delivery-option-external">
				<p>
					<label for="efpic_delivery_external_url"><?php _e( 'External URL', 'efpic-pro' ); ?>:</label>
					<input type="text" name="efpic_delivery_external_url" id="efpic_delivery_external_url" value="<?php echo $external_url; ?>" />
				</p>
				<p class="efpic-hint"><?php _e( 'Enter URL to an external ZIP file, host externally, eg. on Dropbox or Google Drive.', 'efpic-pro' ); ?></p>
			</div>
		</div><!-- .efpic-postbox-inner -->
	</div><!-- .postbox.efpic-postbox -->
<?php
	echo ob_get_clean();

	efpic_display_share_options_form( $post );

	// Display approved view
	if ( function_exists( 'efpic_has_collection_been_closed' ) && efpic_has_collection_been_closed( $post->ID ) ) {
		efpic_display_approved_view( $post );
	}
}


/**
 * Display the delivered view on the collection edit screen.
 *
 * @since delivery (1.0.0)
 *
 * @param object $post The collection post object
 */
function efpic_display_delivered_view( $post ) {
	$delivery_email = get_post_meta( $post->ID, '_efpic_delivery_email_address', true );
	$delivery_url = get_post_meta( $post->ID, '_efpic_collection_delivery_url', true );

	// Prepare icon and message
	if ( get_post_meta( $post->ID, '_efpic_collection_delivery_option', true ) == 'upload' ) {

		$delivery_data = get_post_meta( $post->ID, '_efpic_collection_delivery_ids', true );

		if ( ! empty( $delivery_data ) ) {
			$delivery_image_ids = explode( ',', $delivery_data );
			$delivery_image_count = count( $delivery_image_ids );

			if ( ! empty( $delivery_email ) ) {
				$message = sprintf( _n( 'Your image has been sent to %2$s.', '%1$s images have been sent to %2$s.', $delivery_image_count, 'efpic-pro' ), $delivery_image_count, $delivery_email );
			}
			else {
				$message = sprintf( _n( 'Your image has been sent.', '%s images have been sent.', $delivery_image_count, 'efpic-pro' ), $delivery_image_count );
			}

			if ( $delivery_image_count > 1 ) {
				$icon = 'dashicons-images-alt2';
			}
			else {
				$icon = 'dashicons-format-image';
			}
		}
	}
	else {
		if ( ! empty( $delivery_url['url'] ) AND 'zip' == pathinfo( $delivery_url['url'], PATHINFO_EXTENSION ) ) {
			$icon = 'dashicons-media-archive';
		}
		else {
			$icon = 'dashicons-media-default';
		}

		$delivery_method = get_post_meta( $post->ID, '_efpic_collection_share_method', true );

		if ( ! empty( $delivery_method ) AND $delivery_method == 'efpic-send-email' ) {
			/* translators: %s is one or multiple comma seperated email addresses */
			$message = sprintf ( __( 'Your delivery has been sent to %s.', 'efpic-pro' ), $delivery_email );
		}
		else {
			$message = __( 'Your delivery has been sent to the client.', 'efpic-pro' );
		}
	}
?>
	<div class="postbox efpic-postbox efpic-gallery-has-images">
		<div class="efpic-postbox-inner">
			<h2><?php _e( 'Delivery', 'efpic-pro' ); ?></h2>

			<div class="efpic-delivery-summary">
				<div class="efpic-delivered-status-details">
					<div class="dashicons <?php echo $icon; ?>"></div>
					<div class="efpic-delivered-message"><?php echo $message; ?></div>
				</div><!-- .efpic-delivered-status-details -->
			</div><!-- .efpic-delivery-summary -->

		<?php
			$tracking_data = get_post_meta( $post->ID, '_efpic_delivery_download_tracking', true );

			if ( ! empty( $tracking_data ) AND is_array( $tracking_data ) ) {
		?>

			<input type="checkbox" class="efpic-toggle-download-history-toggle" id="efpic-toggle-download-history" autocomplete="off" />
			<div class="efpic-toggle-download-history">
				<label class="efpic-toggle-show-download-history" for="efpic-toggle-download-history"><?php _e( 'Show Download History', 'efpic-pro' ); ?></label>
				<label class="efpic-toggle-hide-download-history" for="efpic-toggle-download-history" for=""><?php _e( 'Hide Download History', 'efpic-pro' ); ?></label>
			</div>

			<div class="efpic-delivery-download-history">
				<table class="efpic-delivery-download-history-table">
					<thead>
						<tr>
							<th class="efpic-delivery-download-history-table-date"><?php _e( 'Date/Time', 'efpic-pro' ); ?></th>
							<th class="efpic-delivery-download-history-table-name"><?php _e( 'Name', 'efpic-pro' ); ?></th>
						</tr>
					<tbody>
				<?php
					foreach( array_reverse( $tracking_data ) as $single_data_point ) {
						echo '<tr>';

						echo '<td class="efpic-delivery-download-history-table-date">' . wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $single_data_point['time'] ) . '</td>';

						if ( is_numeric( $single_data_point['download'] ) ) {

							$file = wp_get_attachment_image_src( $single_data_point['download'], 'full' );
							$filename = pathinfo( $file[0], PATHINFO_BASENAME );

							echo '<td class="efpic-delivery-download-history-table-name"><a href="' . $file[0] . '" download>' . $filename . '</a></td>';
						}
						else {
							// Display actual zip filename or use external url
							$url = '';

							if ( ! empty( $delivery_url['url'] ) ) {

								if ( 'zip' == pathinfo( $delivery_url['url'], PATHINFO_EXTENSION) ) {
									$url = '<a href="' . $delivery_url['url'] . '">' . wp_basename( $delivery_url['url'] ) . '</a>';
								}
								else {
									$url = esc_url( $delivery_url['url'] );

									// Truncate very long URLs?
									// if ( strlen( $url ) > 100 ) {
									// 	$url = '<a href="' . $temp['url'] . '" title="' . $url . '">' . substr( esc_url( $temp['url'] ), 0, 100 ) . '&hellip;</a>';
									// }
									$url = '<a href="' . $delivery_url['url'] . '" title="' . $url . '">' . esc_url( $delivery_url['url'] ) . '&hellip;</a>';
								}
							}
						
							echo '<td class="efpic-delivery-download-history-table-name">' . $url . '</td>';
						}
						echo '</tr>';
					}
				?>
					</tbody>
				</table>
			</div><!-- .efpic-delivery-download-history -->
		<?php }
			else {
		?>
				<p class="efpic-delivery-no-download-history"><?php _e( 'No downloads yet', 'efpic-pro' ); ?>.</p>
		<?php
			}
		?>
		</div><!-- .efpic-postbox-inner -->
	</div><!-- .postbox.efpic-postbox -->
<?php
	echo ob_get_clean();

	// Display approved view
	if ( function_exists( 'efpic_has_collection_been_closed' ) && efpic_has_collection_been_closed( $post->ID ) ) {
		efpic_display_approved_view( $post );
	}
}