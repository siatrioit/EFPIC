<?php
/**
 * Import via AJAX
 *
 * @since import (0.0.1)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Do the import.
 *
 * @since import (0.0.1)
 */
function efpic_import_import_files() {
	// Nonce check!
	if ( ! check_ajax_referer( 'efpic-import-files', 'security' ) ) {
		$return = array(
			'message' => __( 'Error: Nonce check failed.', 'efpic-pro' )
		);
		wp_send_json_error( $return );
		exit;
	}

	// Check if a folder was selected
	if ( ! isset( $_POST['folder'] ) OR empty( $_POST['folder'] ) ) {
		$return = array(
			'message' => __( 'Please select an import folder.', 'efpic-pro' )
		);
		wp_send_json_error( $return );
		exit;
	}

	// Escape stuff
	$post_id = intval( $_POST['post_id'] );
	$step = intval( $_POST['step'] );

	$folder = $_POST['folder'];

	// Get path to upload directory
	$wp_upload_dir = wp_upload_dir();

	// Get path to efpic import folder
	$import_folder = $wp_upload_dir['basedir'] . '/efpic/import/' . $folder;

	// Check if the selected folder exists
	if ( ! is_dir( $import_folder ) ) {
		$return = array(
			'message' => __( 'The folder you selected does not exist.', 'efpic-pro' )
		);
		wp_send_json_error( $return );
		exit;
	}

	// Get path to efpic collection base folder
	$collection_base_folder = $wp_upload_dir['basedir'] . '/efpic/collections';

	// Check if collection folder exists, if not create it
	if ( ! is_dir( $collection_base_folder ) ) {
		mkdir( $collection_base_folder, 0755 );
	}

	// Get path to efpic collection folder
	$collection_folder = $collection_base_folder .'/' . $_POST['post_id'];

	// Check if collection folder exists, if not create it
	if ( ! is_dir( $collection_folder ) ) {
		mkdir( $collection_folder, 0755 );
	}

	// If is delivery collection check if delivery sub folder exists, also update $collection_folder
	if ( ! empty( $_POST['is_delivery'] ) AND $_POST['is_delivery'] == 1 ) {
		$collection_folder = $collection_base_folder .'/' . $_POST['post_id'] . '/delivery';

		if ( ! is_dir( $collection_folder ) ) {
			mkdir( $collection_folder, 0755 );
		}
	}

	// Get folder content
	$files = scandir( $import_folder );

	// Get rid of all files, that are not images (gif, png, jpg)
	function efpic_import_images_only( $possible_image ) {
		$allowed = array( 'gif', 'GIF', 'png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG' );
		return ( in_array( pathinfo( $possible_image, PATHINFO_EXTENSION ), $allowed ) ) ? true : false;
	}

	function efpic_list_not_images( $possible_image ) {
		$allowed = array( 'gif', 'GIF', 'png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG' );
		return ( ! in_array( pathinfo( $possible_image, PATHINFO_EXTENSION ), $allowed ) ) ? true : false;
	}

	$not_to_be_imported = array_values( array_filter( $files, 'efpic_list_not_images' ) );
	$not_to_be_imported = array_diff( $not_to_be_imported, array( '..', '.', '.DS_Store' ) );
	$not_to_be_imported = array_values( $not_to_be_imported );

	$files = array_values( array_filter( $files, 'efpic_import_images_only' ) );

	// Get number of images
	$number = count( $files );

	// Check if there are too many images
	$max_input_vars = ini_get( "max_input_vars" );

	if ( ! empty( $max_input_vars ) AND $number > $max_input_vars ) {
		$return = array(
			/* translators: admin notice */
			'message' => __( 'The selected import folder contains too many images.', 'efpic-pro' ) . ' <a href="https://efpic.io/docs/pro/import/#too-many-images" target="_blank" rel="noopener">' . __( 'Learn how to fix this issue', 'efpic-pro' ) . '</a>'
		);
		wp_send_json_error( $return );
		exit;		
	}

	// Check if a folder was selected
	if ( 0 >= $number ) {
		$return = array(
			'message' => __( 'The folder you selected does not contain any images.', 'efpic-pro' )
		);
		wp_send_json_error( $return );
		exit;
	}

	// Process the uploaed file
	$percentage = floor( 100 * $step / $number );

	// We have some importing to do
	if ( $step <= $number ) {

		// Get file for the current step
		$file = $files[$step - 1];

		// Get path and extension
		$pathinfo = pathinfo( $file );
		// $extension = $pathinfo['extension'];

		$filename = sanitize_file_name( $pathinfo['basename'] );

		// Copy file into collection folder
		if ( copy( $import_folder . '/' . $pathinfo['basename'], $collection_folder . '/' . $filename ) ) {

			// Prepare file attachment
			$filetype = wp_check_filetype( basename( $filename ), null );

			// Add watermark – if one is set
			if ( function_exists( 'efpic_apply_watermark' ) ) {
				$upload = array(
					'file' => $collection_folder . '/' . $filename,
					'type' => $filetype['type'],
				);
				efpic_apply_watermark( $upload, $post_id, true );
			}

			$attachment = array(
				'guid'           => $collection_folder . '/' . $filename,
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			// Insert the attachment.
			$attach_id = wp_insert_attachment( $attachment, $collection_folder . '/' . $filename, $post_id );

			// wp_generate_attachment_metadata() depends on this file
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $collection_folder . '/' . $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			// Collect image ids of newly added images
			$temporary_info = array( 'step' => $step , 'attach_id' => $attach_id, 'folder' => $_POST['folder'] );
			add_post_meta( $post_id, '_efpic_import_temporary_id', $temporary_info );

		} // if copied
		else {
			$return = array(
				'message' => __( 'Error: File could not be copied.', 'efpic-pro' )
			);
			wp_send_json_error( $return );
			exit;
		}

		// Return success message and file infos
		$return = array(
			'message' => __( 'Importing', 'efpic-pro' ),
			'next_step' => $step + 1,
			'filename' => $files[$step - 1],
			'percentage' => $percentage,
		);

		wp_send_json_success( $return );
	}
	// We are done importing!
	else {

		// Get import settings (delete vs. move)
		$import_option = get_option( 'efpic_import_file_handling', 'keep_files' );

		// Set notification variable
		$folder_notification = '';

		// Move source folder into _imported folder
		if ( $import_option == 'move_files' ) {

			// Get / create _imported folder
			$imported = $wp_upload_dir['basedir'] . '/efpic/_imported';

			if ( ! is_dir( $imported ) ) {
				mkdir( $imported, 0755 );
			}

			$imported_folder = $imported . '/' . $folder;

			// Recursively check if folder exists, if so, add -1 at the end
			while ( is_dir( $imported_folder ) ) {
				 $imported_folder = $imported_folder . '-1';
			 }

			 rename( $import_folder, $imported_folder );

			 $folder_notification = '<br />' . __( 'The source folder has been moved into the <code>_imported</code> folder.', 'efpic-pro' );
		}

		// Delete source folder
		elseif ( $import_option == 'delete_files' ) {
			// Check if the path we get actually is a directory
			if ( is_dir( $import_folder ) ) {

				foreach( $files as $file ) {
					if ( ! unlink( $import_folder . '/' . $file ) ) {
						// Remember that a file could not be deleted
						$folder_not_deleted = false;
					}
				}

				// Delete .DS_Store file
				unlink( $import_folder . '/.DS_Store' );

				// Remove the source directory
				if ( rmdir( $import_folder ) ) {
					$folder_notification = '<br />' . __( 'Source files and folder deleted.', 'efpic-pro' );
				}
				else {
					// Remember the folder could not be deleted
					$folder_not_deleted = false;
				}
			}

		}

		// Get imported image ids
		$new_image_ids = array();
		$temporary_info = get_post_meta( $post_id, '_efpic_import_temporary_id' );

		foreach( $temporary_info as $temp ) {
			$new_image_ids[] = $temp['attach_id'];
		}

		$new_image_ids = implode( ',', $new_image_ids );

		// Check if there are images saved before, if so add the new ones at the end
		if ( ! empty( $_POST['is_delivery'] ) AND $_POST['is_delivery'] == 1 ) {
			$collection_gallery_ids = get_post_meta( $post_id, '_efpic_collection_delivery_ids', true );
		}
		else {
			$collection_gallery_ids = get_post_meta( $post_id, '_efpic_collection_gallery_ids', true );
		}

		if ( ! empty( $collection_gallery_ids ) ) {
			$new_image_ids = $collection_gallery_ids . ',' . $new_image_ids;
		}

		// This is probably not even needed, as we update the hidden input field
		// on the collection edit screen with all the ids, and then save them, which
		// overwrites this update anyways.
		// But: It could be a nice way to already set this here, in case something goes wrong
		// on the 'last mile'.
		if ( ! empty( $_POST['is_delivery'] ) AND $_POST['is_delivery'] == 1 ) {
			update_post_meta( $post_id, '_efpic_collection_delivery_ids', $new_image_ids );
		}
		else {
			update_post_meta( $post_id, '_efpic_collection_gallery_ids', $new_image_ids );
		}

		// Delete temporary image ids
		delete_post_meta( $post_id, '_efpic_import_temporary_id' );

		// Add success (and other) notifications
		efpic_add_notification( 'efpic_import_success', 'notice notice-success is-dismissible', '<strong>' . __( 'Import successful.', 'efpic-pro' ) . '</strong> ' . $folder_notification );

		// Add message, if some files could not be imported
		if ( 0 < count( $not_to_be_imported ) ) {
			$temp = '';
			if ( 'move' == $import_options['file_handling'] ) {
				$temp = __( 'These files have also been moved to the <code>_imported</code> folder.', 'efpic-pro' );
			}
			if ( 'delete' == $import_options['file_handling'] ) {
				$temp = __( 'Thus, the source folder has not been deleted.', 'efpic-pro' );
			}

			efpic_add_notification( 'efpic_import_files', 'notice notice-warning is-dismissible', sprintf( __( 'Some files (eg. %s) could not be imported.', 'efpic-pro' ), $not_to_be_imported[0] ) . $temp . ' <a href="https://efpic.io/docs/pro/import/#some-files-could-not-be-imported" target="_blank" rel="noopener">' . __( 'More info', 'efpic-pro' ) . '</a>' );
		}
		elseif( isset( $folder_not_deleted ) and false == $folder_not_deleted ) {
			efpic_add_notification( 'efpic_import_folder_delete_error', 'notice notice-warning is-dismissible', __( 'There was an error deleting the source folder.', 'efpic-pro' ) . ' <a href="https://efpic.io/docs/pro/import/#source-folder-could-not-be-deleted" target="_blank" rel="noopener">' . __( 'More info', 'efpic-pro' ) . '</a>' );
		}

		// Send success message
		$return = array(
			'message' => __( 'Finished importing!', 'efpic-pro' ),
			'next_step' => 'done',
			'image_ids' => $new_image_ids,
			'post_id' => $post_id
		);

		wp_send_json_success( $return );
	}
}

add_action( 'wp_ajax_efpic_import_import_files', 'efpic_import_import_files' );
add_action( 'wp_ajax_nopriv_efpic_import_import_files', 'efpic_import_import_files' );


/**
 * Cancel import.
 *
 * @since import (0.0.2)
 */
function efpic_import_cancel_import() {
	// Nonce check!
	if ( ! check_ajax_referer( 'efpic-import-files', 'security' ) ) {
		$return = array(
			'message' => __( 'Error: Nonce check failed.', 'efpic-pro' )
		);
		wp_send_json_error( $return );
		exit;
	}

	// Make sure we have the collection ID
	if ( ! isset( $_POST['post_id'] ) OR empty( $_POST['post_id'] ) ) {
		$return = array(
			'message' => __( 'Collection ID is missing. Please reload the page and try again.', 'efpic-pro' )
		);
		wp_send_json_error( $return );
		exit;
	}

	// Get post id
	$post_id = intval( $_POST['post_id'] );

	// Get image ids
	$lonely_images = get_post_meta( $post_id, '_efpic_import_temporary_id' );

	// Are there any images?
	if ( 0 < count( $lonely_images ) ) {

		// Delete images
		foreach ( $lonely_images as $lonely_image ) {
			wp_delete_attachment( $lonely_image['attach_id'], true );
		}

		// Delete meta entries
		delete_post_meta( $post_id, '_efpic_import_temporary_id' );
	}

	// Send success message
	$return = array(
		'message' => __( 'Import canceled.', 'efpic-pro' ),
		'post_id' => $post_id
	);
	wp_send_json_success( $return );
}

add_action( 'wp_ajax_efpic_import_cancel_import', 'efpic_import_cancel_import' );
add_action( 'wp_ajax_nopriv_efpic_import_cancel_import', 'efpic_import_cancel_import' );