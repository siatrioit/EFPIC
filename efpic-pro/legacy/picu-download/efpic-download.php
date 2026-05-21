<?php
/**
 * Download
 *
 * Enables downloading of collection images.
 *
 * @since download (0.0.1)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Add Download body class.
 *
 * @since download (0.0.2)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param array $body_classes The body classes
 * @return array The filtered classes
 */
function efpic_download_add_image_size_body_class( $body_classes ) {
	$body_classes[] = 'efpic-download';
	return $body_classes;
}

add_filter( 'efpic_body_classes', 'efpic_download_add_image_size_body_class');


/**
 * Add download button to header.
 *
 * @since download (0.0.2)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param string $efpic_header The header HTML
 * @param int $post_ID The collection post ID
 * @return string The filtered header HTML
 */
function efpic_download_header_button( $efpic_header, $post_ID ) {
	if ( get_post_status( $post_ID ) == 'delivered' OR get_post_status( $post_ID ) == 'delivery-draft' ) {
		return $efpic_header;
	}

	$dl = get_post_meta( $post_ID, '_efpic_collection_download_images', true );

	if ( ! empty( $dl ) AND $dl['option'] == 'zip' ) {
		add_action ( 'efpic_app_state', function( $state ) { $state['is_zip_download_enabled'] = true; return $state; } );
	}

	if ( isset( $dl['option'] ) AND $dl['option'] == 'url' ) {
		return $efpic_header . '<a class="efpic-download-button" href="'. esc_url( $dl['url'] ) . '"><span>' . __( 'Download', 'efpic-pro' ) . '</span><svg viewBox="0 0 100 100"><use xlink:href="#icon-download"></use></svg></a>';
	}
	elseif ( isset( $dl['option'] ) AND $dl['option'] == 'zip' AND isset( $dl['url'] ) AND ! empty( $dl['url'] ) AND class_exists( 'ZipArchive' ) ) {
		return $efpic_header . '<div class="efpic-download-options"><span class="efpic-download-label">' . __( 'Download', 'efpic-pro' ) . '</span> <a class="efpic-download-button" href="'. esc_url( $dl['url'] ) . '"><span>' . __( 'All', 'efpic-pro' ) . '</span><svg viewBox="0 0 100 100"><use xlink:href="#icon-download"></use></svg></a><a class="efpic-download-button download-selected js-download-selected" href="#"><span>' . __( 'Selected', 'efpic-pro' ) . '</span><span class="efpic-download-selected-num"></span><svg viewBox="0 0 100 100"><use xlink:href="#icon-download"></use></svg></a></div>';
	}
	// Zip file is not ready yet
	elseif ( isset( $dl['option'] ) AND $dl['option'] == 'zip' AND empty( $dl['url'] ) ) {
		return $efpic_header . '<a class="efpic-download-button js-efpic-download-zip-not-ready" href="#"><span>' . __( 'Download ZIP', 'efpic-pro' ) . '</span><svg viewBox="0 0 100 100"><use xlink:href="#icon-download"></use></svg></a>';
	}

	return $efpic_header;
}

add_action( 'efpic_header', 'efpic_download_header_button', 99, 2 );


/**
 * Show modal, if download file is not ready yet.
 * 
 * @since download (1.0.3)
 * 
 * @see efpic/frontend/efpic-app.php
 * @global object $post The collection post object
 * 
 * @param string $scripts The script code
 * @return string The filtered scripts code
 */
function efpic_download_not_ready_yet( $scripts ) {
	global $post;

	$dl = get_post_meta( $post->ID, '_efpic_collection_download_images', true );

	if ( isset( $dl['option'] ) AND $dl['option'] == 'zip' AND ! isset( $dl['url'] ) ) {

	$scripts .= '<script>$( \'.js-efpic-download-zip-not-ready\' ).click( function( e ) {
			e.preventDefault();
			$( \'.efpic-collection\' ).append( \'<div class="overlay success"><div class="message error"><p>' . __( 'The .zip file will be generated, when you send the collection to the client.', 'efpic-pro' ) . '</p><p><a class="efpic-button small primary js-close-message">OK</a></p></div></div>\' );
		});</script>';

	}

	return $scripts;
}

add_action( 'efpic_custom_scripts', 'efpic_download_not_ready_yet' );


/**
 * Add script to trigger background zip generation.
 *
 * @since download (1.1.0)
 *
 * @see efpic/frontend/efpic-app.php
 * @global object $post The collection post object
 * 
 * @param string $scripts The script code
 * @return string The filtered scripts code
 */
function efpic_initate_background_zip( $scripts ) {
	global $post;

	$dl = get_post_meta( $post->ID, '_efpic_collection_download_images', true );

	// Only add script, if download option is enabled
	if ( isset( $dl['option'] ) AND $dl['option'] == 'zip' AND $post->post_status != 'delivery-draft' AND $post->post_status != 'delivered' ) {

		$scripts .= '<script>
		efpic.createZip = function() {

			temp = JSON.parse( appstate );
		
			// Send AJAX request
			$.post( temp.ajaxurl, {
				action: \'efpic_create_zip\',
				security: temp.nonce,
				postid: temp.postid,
				selection: _.map( efpic.collection.where({selected: true}), function( s ){ return s.attributes.imageID; }),
			}, function( response ) {
				// Display overlay if saving failed
				var overlayclass = \'\';
				if ( response.success == true ) {
					window.location = response.data.dl_url;
					$( \'.js-download-selected\' ).removeClass( \'processing\' );
				} else {
					overlayclass = \' fail\';
					$( \'.efpic-collection\' ).append( \'<div class="overlay\'+ overlayclass +\'"><div class="message"><p>\' + response.data.message + \'</p><p><a class="efpic-button small primary js-close-message">\' + response.data.button_text + \'</a></p></div></div>\' );
					$( \'.js-download-selected\' ).removeClass( \'processing\' );
				}
			}).fail( function() {
				// Ajax fail
				$( \'.efpic-collection\' ).append( \'<div class="overlay fail"><div class="message"><p>Error: Request failed.<br />Do you have a working internet connection?</p><p><a class="efpic-button small primary js-close-message" href="#">OK</a></p></div></div>\' );
			});
		}
		
		$( \'.js-download-selected\' ).click( function( e ) {
			e.preventDefault();
			efpic.createZip();
			$( this ).addClass( \'processing\' );
		});
		
		</script>';
	}

	return $scripts;
}

add_action( 'efpic_custom_scripts', 'efpic_initate_background_zip' );


/**
 * Trigger zip file generation via AJAX.
 * 
 * @since download (1.1.0)
 */
function efpic_trigger_zip() {
	// Nonce check!
	if ( ! check_ajax_referer( 'efpic-ajax-security', 'security', false ) ) {
		$return = array(
			/* Uses efpic core text domain, will be translated over there  */
			'message' => __( '<strong>Error:</strong> Nonce check failed.<br />Refresh your browser window.', 'efpic' ),
			'button_text' => __( 'OK', 'efpic' )
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

	// Sanitize selection
	if ( isset( $_POST['selection'] ) AND ! empty( $_POST['selection'] ) ) {
		$temp = $_POST['selection'];
		$selection = array();
		foreach ( $temp as $id ) {
			// Ids must be integer values, handing them over as strings
			$selection[] = strval( intval( $id ) );
		}
	}
	else {
		$selection = false;
		$return = array(
			/* Uses efpic core text domain, will be translated over there  */
			'message' => __( 'No images selected.', 'efpic-pro' ),
			'button_text' => __( 'OK', 'efpic' )
		);
		wp_send_json_error( $return );
		exit;
	}

	// Create zip file
	$dl_url = efpic_create_download_zip( $post_id, $selection );

	// It worked
	if ( $dl_url ) {
			// Return some info and the download url
			$return = array(
				'message' => __( 'Zip file created.', 'efpic-pro' ),
				'dl_url' => $dl_url,
				'button_text' => __( 'OK', 'efpic' )
			);
			wp_send_json_success( $return );

	}
	// It didn't work
	else {
		// Return error message
		$return = array(
			'message' => __( 'Error. Zip file could not be created.', 'efpic-pro' ),
			'button_text' => __( 'OK', 'efpic' )
		);
		wp_send_json_error( $return );
	}

	exit;
}

add_action( 'wp_ajax_efpic_create_zip', 'efpic_trigger_zip' );
add_action( 'wp_ajax_nopriv_efpic_create_zip', 'efpic_trigger_zip' );


/**
 * Collection option output.
 *
 * @since download (0.0.1)
* @see efpic/backend/includes/efpic-edit-collection.php
 * @global object $post The post object
 *
 * @param array $option_output Collection options, the key is used as div id
 * @return array The filtered options
 */
function efpic_download_add_collection_option( $option_output ) {
	global $post;

	$dl = get_post_meta( $post->ID, '_efpic_collection_download_images', true );

	if ( ! is_array( $dl ) ) {
		$dl = array(
			'option' => false,
			'url' => ''
		);
	}

	$download = false;

	if ( 'zip' == $dl['option'] OR 'url' == $dl['option'] ) {
		$download = true;
	}

	// Disable option when collection has been sent
	$disabled = ( 'sent' == $post->post_status ) ? ' disabled' : '';
	$zip_disabled = $disabled;
	$url_disabled = $disabled;

	// Generate option output
	ob_start();

	echo '<p><input type="checkbox" class="js-collapse-control" id="efpic_download_images" name="efpic_download_images" ' . checked( true, $download, false ) . ' ' . $disabled . ' autocomplete="off" /> <label for="efpic_download_images">' . __( 'Enable image download', 'efpic-pro' ) . '&hellip;</label></p>';
	echo '<div class="js-collapsible';

	if ( 'zip' != $dl['option'] AND 'url' != $dl['option'] ) {
		echo ' is-collapsed';
		$dl['option'] = 'zip';
	}

	// Check if zip-class is available
	$zip_disabled_message = '';
	$zip_disabled_label_class = '';

	if ( ! class_exists( 'ZipArchive' ) ) {
		$dl['option'] = 'url';
		$zip_disabled = ' disabled';
		$zip_disabled_message = '<span class="efpic-php-zip-error">Not supported. <a class="efpic-help" href="https://efpic.io/docs/pro/download/#zip-not-supported" target="_blank">' . __( 'Learn more', 'efpic-pro' ) . '</a></span>';
		$zip_disabled_label_class = 'class="efpic-php-zip-error-disabled" ';
	}

	echo '" id="efpic-download-images-options">';
	echo '<p><input type="radio" id="efpic_image_download_zip" name="efpic_download_option" value="zip"' . $zip_disabled . ' ' . checked( 'zip', $dl['option'], false ) . ' autocomplete="off" /> <label ' . $zip_disabled_label_class . ' for="efpic_image_download_zip">' . __( 'Automatically create .zip file from collection', 'efpic-pro' ) . '</label>' . $zip_disabled_message . '</p>';
	echo '<p><input type="radio" id="efpic_image_download_url" name="efpic_download_option" value="url"' . $url_disabled . ' ' . checked( 'url', $dl['option'], false ) . ' autocomplete="off" /> <label for="efpic_image_download_url">' . __( 'Use external URL', 'efpic-pro' ) . '</label> <input style="width: 240px" type="text" name="efpic_image_download_src" placeholder="http://domain.tld/photos.zip" value="';

	if ( isset( $dl['option'] ) AND 'url' == $dl['option'] AND isset( $dl['url'] ) AND ! empty( $dl['url'] ) ) {
		echo $dl['url'];
	}

	echo '" ' . $disabled . ' autocomplete="off" /></p></div>';

	$option_output['efpic-download-images'] = ob_get_clean();

	return $option_output;
}

add_filter( 'efpic_collection_options', 'efpic_download_add_collection_option', 100 );


/**
 * Create zip file from collection images.
 *
 * The zip file will only be created, when the collection is sent/published.
 *
 * @since download (0.0.1)
 *
 * @param int|bool $post_id The collection post ID
 * @param array $img_ids IDs of the image to be zipped
 * @return string|bool The zip URL or false
 */
function efpic_create_download_zip( $post_id = false, $img_ids = [] ) {
	if ( ! $post_id ) {
		$post = get_post();
		$post_id = $post->ID;
	}
	
	if ( ! class_exists( 'ZipArchive' ) ) {
		return false;
	}

	// Setting image ids when calling this function from the front end to generate the selected zip
	if ( empty( $img_ids ) ) {
		// Get image ids from meta
		$efpic_gallery_ids = get_post_meta( $post_id, '_efpic_collection_gallery_ids', true );
	}
	else {
		$efpic_gallery_ids = implode( ',', $img_ids );
	}

	// Only create zip, if there are images, duh!
	if ( ! empty( $efpic_gallery_ids ) ) {

		// Check if a zip file for this collection already exists. If so, delete it.
		// We need to remove our pre_get_posts filter first, to actually get collection attachments :-)
		remove_action( 'pre_get_posts', 'efpic_exclude_collection_images_from_library', 999 );
		$existing_zip_files = get_attached_media( 'application/zip', $post_id );
		add_action( 'pre_get_posts', 'efpic_exclude_collection_images_from_library', 999 );

		// Delete the respecive previous version of a zip file before creating a new one
		// Delete the zip file containing selected images
		if ( is_array( $existing_zip_files ) AND ! empty( $img_ids ) ) {

			foreach( $existing_zip_files as $zip_file ) {
				// Delete old version of zip file aka files which names contain "_selection"
				if ( ! empty( $img_ids ) AND strpos( $zip_file->post_name, '_selection' ) ) {
					wp_delete_attachment( $zip_file->ID, true );
				}
			}
		}
		// Delete the zip file containing all images
		else {
			foreach( $existing_zip_files as $zip_file ) {
				if( $zip_file->post_title == sanitize_file_name( get_the_title( $post_id ) ) ) {
					wp_delete_attachment( $zip_file->ID, true );
				}
			}
		}

		// Let's create a fresh zip
		$zip = new ZipArchive();

		// Get path (where collection images are saved)
		$upload_dir = wp_upload_dir();
		$dir = $upload_dir['basedir'] . '/efpic/collections/' . $post_id . '/';

		if ( ! $img_ids ) {
			$filename =  $dir . sanitize_file_name( get_the_title( $post_id ) ) . '.zip';
		}
		else {
			$filename =  $dir . sanitize_file_name( get_the_title( $post_id ) ) . '_selection.zip';
		}

		if ( ! $img_ids ) {
			$zipurl = $upload_dir['baseurl'] . '/efpic/collections/' . $post_id . '/' . sanitize_file_name( get_the_title( $post_id ) ) . '.zip';
		}
		else {
			$zipurl = $upload_dir['baseurl'] . '/efpic/collections/' . $post_id . '/' . sanitize_file_name( get_the_title( $post_id ) ) . '_selection.zip';
		}

		if ( $zip->open( $filename, ZipArchive::CREATE ) !== true ) {
			exit( "Cannot open <$filename>\n" );
		}

		// Make array of image ids
		$images = explode( ',', $efpic_gallery_ids );

		// Prefix filenames with a number
		// First, we need to know the string length of the highest number, to add the right amount of leading zeros:
		$leadingzeros = strlen( count( $images ) );

		// We start at 1
		$i = 1;

		// Loop through all images
		foreach( $images as $image ) {

			// Do not add numbers in front of file names
			if ( ! empty( $img_ids ) ) {
				$file = basename( get_attached_file( $image ) );
			}
			else {
				// Default prefix: number with leading zero(s) and a dash
				$default_prefix = sprintf( '%0' . $leadingzeros . 'd', $i ) . '-';

				// Filter the prefix
				$prefix = apply_filters( 'efpic_download_file_name_prefix', $default_prefix, $i, $images );

				// Add the number (with leading zeros) and a dash as a prefix:
				$file = $prefix . basename( get_attached_file( $image ) );
			}

			// Add image to the zip file
			$zip->addFile( get_attached_file( $image ), $file );
			$i++;
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

		wp_insert_attachment( $attachment, $filename, $post_id );

		return $zipurl;
	}

	return false;
}


/**
 * Sanitize & save options.
 *
 * @since download (0.0.1)
 *
 * @param int $post_id The collection post ID
 * @param object $post_ The collection post object
 */
function efpic_download_save_collection( $post_id, $post ) {

	// Abort if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

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

	// Check options, save as meta data accordingly
	if ( isset( $_POST['efpic_download_images'] ) AND 'on' == $_POST['efpic_download_images'] ) {

		if ( isset( $_POST['efpic_download_option'] ) AND 'zip' == $_POST['efpic_download_option'] ) {

			$temp = array(
				'option' => 'zip'
			);

			// Only generate zip file if the user is actually sending the collection
			// Make sure this only run, when post status is "sent"
			if ( 'sent' == $post->post_status AND isset( $_REQUEST['efpic_sendmail'] ) ) {
				$url = efpic_create_download_zip();
				$temp['url'] = esc_url_raw( $url );
			}

		}
		elseif ( isset( $_POST['efpic_download_option'] ) AND 'url' == $_POST['efpic_download_option'] ) {

			$temp = array( 'option' => 'url' );

			if ( isset( $_POST['efpic_image_download_src'] ) AND ! empty( $_POST['efpic_image_download_src'] ) ) {
				$temp['url'] = esc_url( $_POST['efpic_image_download_src'] );
			}
			else {
				efpic_add_notification( 'efpic_download_missing_url', 'notice notice-error is-dismissible', __( 'Please enter a download URL.', 'efpic-pro' ) );
			}
		}

		update_post_meta( $post_id, '_efpic_collection_download_images', $temp );
	}
	else {
		delete_post_meta( $post_id, '_efpic_collection_download_images' );
	}
}

add_action( 'save_post_efpic_collection', 'efpic_download_save_collection', 9, 2 );