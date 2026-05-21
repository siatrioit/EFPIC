<?php
/**
 * Mark & Comment
 *
 * Enables clients to place markers & comment on images.
 *
 * @since mark-comment (0.0.1)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Initialize Mark & Comment.
 *
 * @since mark-comment (0.0.1)
 */
require_once EFPIC_PRO_PATH . 'legacy/efpic-mark-comment/includes/approved-view.php';


/**
 * Check if collection uses Mark & Comment
 *
 * @since 1.0.0
 *
 * @global object $post The post Object
 *
 * @return bool Whether the collection uses Mark & Comment
 */
function efpic_pro_collection_uses_mark_comment() {
	global $post;

	if ( get_post_meta( $post->ID, '_efpic_collection_mark_comment', true ) == 1 ) {
		return true;
	}

	return false;
}


/**
 * Replace lightbox backbone template.
 *
 * @since mark-comment (0.0.1)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param array $templates The front end backbone templates
 * @return array The filtered templates
 */
function efpic_add_mark_comment_lightbox_template( $templates ) {
	if ( efpic_pro_collection_uses_mark_comment() ) {
		$templates['lightbox'] = EFPIC_PRO_PATH . 'legacy/efpic-mark-comment/templates/lightbox.php';
		$templates['gallery-item'] = EFPIC_PRO_PATH . 'legacy/efpic-mark-comment/templates/gallery-item.php';
	}

	return $templates;
}

add_filter( 'efpic_load_backbone_templates', 'efpic_add_mark_comment_lightbox_template', 10, 1 );


/**
 * Add body class when Mark & Comment is used.
 *
 * @since mark-comment (0.0.1)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param array $body_classes The body classes
 * @return array The filtered classes
 */
function efpic_mark_comment_body_class( $body_classes ) {
	if ( efpic_pro_collection_uses_mark_comment() ) {
		$body_classes[] = 'uses-mark-comment';
	}

	return $body_classes;
}

add_filter( 'efpic_body_classes', 'efpic_mark_comment_body_class' );


/**
 * Add backbone models, collections and views.
 *
 * @since mark-comment (0.0.1)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param array $cmv Models, collections and views
 * @return array Filtered models, collections and views
 */
function efpic_add_mark_comment_lightbox_view( $cmv ) {
	if ( efpic_pro_collection_uses_mark_comment() ) {
		$cmv['mark-comment-model'] = EFPIC_PRO_URL . 'legacy/efpic-mark-comment/templates/model.js?v=' . filemtime( EFPIC_PRO_PATH . 'legacy/efpic-mark-comment/templates/model.js' );
		$cmv['mark-comment-collection'] = EFPIC_PRO_URL . 'legacy/efpic-mark-comment/templates/collection.js?v=' . filemtime( EFPIC_PRO_PATH . 'legacy/efpic-mark-comment/templates/collection.js' );
		$cmv['lightbox-view'] = EFPIC_PRO_URL . 'legacy/efpic-mark-comment/templates/lightbox-view.js?v=' . filemtime( EFPIC_PRO_PATH . 'legacy/efpic-mark-comment/templates/lightbox-view.js' );
	}

	return $cmv;
}

add_filter( 'efpic_load_cmv', 'efpic_add_mark_comment_lightbox_view', 10, 1 );


/**
 * Add item to collection info modal.
 *
 * @since mark-comment (0.0.1)
 *
 * @see efpic/frontend/js/templates/efpic-collection-info.php
 *
 * @param array $panels The modal items
 * @return array Filtered items
 */
function efpic_add_mark_comment_panel_item( $panels ) {
	if ( efpic_pro_collection_uses_mark_comment() ) {
		$panels[] = '<div class="panel-item"><div class="panel-value"><@= comments @></div><div class="panel-label">' . __( 'comments', 'efpic-pro' ) . '</div></div>';
	}

	return $panels;
}

add_filter( 'efpic_info_view_panel_items', 'efpic_add_mark_comment_panel_item' );


/**
 * Add js translation.
 *
 * @since mark-comment (1.1.0)
 *
 * @see efpic/frontend/includes/efpic-template-functions.php
 *
 * @param array $state The app state
 * @return array The filtered app state
 */
function efpic_mark_comment_i18n( $state ) {
	$state['i18n-delete-comment'] = __( 'Delete', 'efpic-pro' );
	$state['i18n-save-comment'] = __( 'Save', 'efpic-pro' );

	return $state;
}

add_filter( 'efpic_app_state', 'efpic_mark_comment_i18n' );


/**
 * Add filter option in the backend approved view.
 *
 * @since mark-comment (1.0.7)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 *
 * @param array $filter_options The filter options
 * @return array The filtered  options
 */
function efpic_mark_comment_table_filter_options( $filter_options ) {
	// Check if current colleection has comments activated
	if ( efpic_pro_collection_uses_mark_comment() ) {
		$filter_options['has_comment'] = __( 'has comment', 'efpic-pro' );
	}

	return $filter_options;
}

add_filter( 'efpic_table_filter_options', 'efpic_mark_comment_table_filter_options' );


/**
 * Add callback function to check if an images has a comment.
 *
 * @since mark-comment (1.0.7)
 *
 * @param int $image_id The image ID
 * @param object $post The collection post object
 * @return bool Whether an image has one or more comments
 */
function efpic_image_has_comment( $image_id, $post ) {
	// For multiple clients
	$hashes = get_post_meta( $post->ID, '_efpic_collection_hashes', true );
	if ( ! empty( $hashes ) AND count( $hashes ) > 0 ) {
		foreach( $hashes as $hash => $value ) {
			$collection_selection = get_post_meta( $post->ID, '_efpic_collection_selection_' . $hash , true );
			if ( ! empty( $collection_selection['markers']['id_' . $image_id] ) ) {
				return true;
			}
		}
	}
	// Only has single recipient
	else {
		$collection_selection = get_post_meta( $post->ID, '_efpic_collection_selection', true );

		if ( ! empty( $collection_selection['markers']['id_' . $image_id] ) ) {
			return true;
		}	
	}

	return false;
}

/**
 * Collection option output.
 *
 * @since mark-comment (0.0.1)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 * @global object $post The post object
 *
 * @param array $option_output Collection options, the key is used as div id
 * @return array The filtered options
 */
function efpic_mark_comment_add_collection_option( $option_output ) {
	global $post;

	$mc = get_post_meta( $post->ID, '_efpic_collection_mark_comment', true );

	if ( $mc != 1 ) {
		$mc = 0;
	}

	// Disable option when collection has been sent
	$disabled = ( 'sent' == $post->post_status ) ? ' disabled' : '';

	// Generate option output
	ob_start();

	echo '<p>';
	echo '<input type="checkbox" id="efpic_mark_comment" name="efpic_mark_comment_allow_comments" autocomplete="off" ' . checked( 1, $mc, false ) . ' ' . $disabled . ' /> <label for="efpic_mark_comment">' . __( 'Enable Comments &amp; Markers', 'efpic-pro' ) . '</label></p>';

	$option_output['efpic-mark-comment'] = ob_get_clean();

	return $option_output;
}

add_filter( 'efpic_collection_options', 'efpic_mark_comment_add_collection_option', 100 );


/**
 * Sanitize & save options.
 *
 * @since mark-comment (0.0.1)
 *
 * @param int $post_id The collection post ID
 */
function efpic_mark_comment_save_collection( $post_id ) {
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
	if ( isset( $_POST['efpic_mark_comment_allow_comments'] ) AND 'on' == $_POST['efpic_mark_comment_allow_comments'] ) {
		$temp = 1;
	}

	if ( isset( $temp ) ) {
		update_post_meta( $post_id, '_efpic_collection_mark_comment', $temp );
	}
	else {
		delete_post_meta( $post_id, '_efpic_collection_mark_comment' );
	}

}

add_action( 'save_post_efpic_collection', 'efpic_mark_comment_save_collection', 9 );


/**
 * Extrend proof file content.
 *
 * @since mark-comment (1.1.1)
 *
 * @param string $proof_file_content The content of the proof file
 * @param int $collection_id The collection post ID
 * @return string The filtered proof file content
 */
function efpic_mark_comment_proof_file_content( $proof_file_content, $collection_id ) {
	$collection_images = get_post_meta( $collection_id, '_efpic_collection_gallery_ids', true );
	$images = explode( ',', $collection_images );

	ob_start();

	// Multi-client collection
	$hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );
	if ( ! empty( $hashes ) AND count( $hashes ) > 0 ) {

		echo "\n";

		// Iterate through recipients
		foreach( $hashes as $hash => $value ) {

			// Get comments
			$meta_data = get_post_meta( $collection_id, '_efpic_collection_selection_' . $hash, true );

			$image_comments = '';

			foreach( $images as $image_id ) {

				// If there are comments (markers) go ahead
				if ( isset( $meta_data['markers']['id_' . $image_id] ) ) {

					$attachment = wp_get_attachment_image_src( $image_id, 'full' );
					$img_filename = pathinfo( $attachment[0], PATHINFO_FILENAME );
					$image_title = apply_filters( 'efpic_approved_filename', $img_filename, $image_id );

					// Iterate through the comments
					$current_image = '';
					foreach( $meta_data['markers']['id_' . $image_id] as $marker ) {
						// Only display image name above the first comment
						if ( $current_image == $image_title ) {
							$image_comments .= "- " . $marker['comment'] . "\n";
						}
						else {
							$image_comments .= "\n" . $image_title . ":\n- " . $marker['comment'] . "\n";
							$current_image = $image_title;
						}
					}
				}
			}

			// Display comments per recipient
			if ( ! empty( $image_comments ) ) {
				echo "* * *\n\n";
				/* translators: %s is a customer name and/or email address */
				echo sprintf( __( 'Individual image comments by %s', 'efpic-pro' ), $value['email'] ) . "\n";
				echo $image_comments;
				echo "\n";
			}
		}
	}
	// Single collection
	else {
		// Get comments
		$meta_data = get_post_meta( $collection_id, '_efpic_collection_selection', true );

		$image_comments = '';

		foreach( $images as $image_id ) {

			// If there are comments (markers) go ahead
			if ( isset( $meta_data['markers']['id_' . $image_id] ) ) {

				$attachment = wp_get_attachment_image_src( $image_id, 'full' );
				$img_filename = pathinfo( $attachment[0], PATHINFO_FILENAME );
				$image_title = apply_filters( 'efpic_approved_filename', $img_filename, $image_id );

				// Iterate through the comments
				$current_image = '';
				foreach( $meta_data['markers']['id_' . $image_id] as $marker ) {
					if ( $current_image == $image_title ) {
						$image_comments .= "- " . $marker['comment'] . "\n";
					}
					else {
						$image_comments .= "\n" . $image_title . ":\n- " . $marker['comment'] . "\n";
						$current_image = $image_title;
					}
				}
			}
		}

		// Display comments per recipient
		if ( ! empty( $image_comments ) ) {
			echo "\n\n* * *\n\n";
			echo __( 'Individual image comments', 'efpic-pro' ) . "\n";
			echo $image_comments;
		}
	}

	return $proof_file_content . ob_get_clean();

}

add_filter( 'efpic_proof_file_content', 'efpic_mark_comment_proof_file_content', 10, 2 );