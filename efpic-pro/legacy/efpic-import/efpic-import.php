<?php
/**
 * Import
 *
 * Enables importing images directly from your webserver.
 *
 * @since import (0.0.1)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Initialize Import.
 *
 * @since import (0.0.1)
 */
require_once EFPIC_PRO_PATH . 'legacy/efpic-import/includes/importer.php';


/**
 * Load scipts in the admin.
 *
 * @since import (0.0.1)
 *
 * @global object $post The collection post ID
 */
function efpic_import_admin_scripts() {
	global $post;

	$current_screen = get_current_screen();

	// Prevent conflicts in case no current_screen is set
	if ( empty($current_screen) )
		return;

	// Only load those styles on edit collection screens
	if ( is_admin() ) {

		global $pagenow;

		if ( ( 'efpic_collection' == $current_screen->post_type AND 'efpic_collection' == get_post_type() AND 'post-new.php' == $pagenow || 'post.php' == $pagenow ) ) {

			// Enqueue our custom script
			wp_enqueue_script( 'efpic-import', EFPIC_PRO_URL . 'legacy/efpic-import/js/efpic-import.min.js', array( 'jquery' ), filemtime( EFPIC_PRO_PATH . 'legacy/efpic-import/js/efpic-import.min.js' ), true );

			$efpic_import_notifications = array(
				'import_error' => '<strong>' . __( 'An error occured.', 'efpic-pro' ) . '</strong>',
				/* translators: 1: open link tag; 2: close link tag. */
				'import_error_save_collection' => '<strong>' . __( 'An error occured.', 'efpic-pro' ) . '</strong> ' . sprintf( __( 'Please %1$ssave the collection%2$s to recover already imported images.', 'efpic-pro' ), '<a class="js-efpic-import-save-collection" href="#">', '</a>' ),
			);

			wp_localize_script( 'efpic-import', 'efpic_import_notifications', $efpic_import_notifications );
		}

	}
}

add_action( 'admin_enqueue_scripts', 'efpic_import_admin_scripts' );


/**
 * Display import dialog box.
 * 
 * It will be shown above the regular upload field, when creating a regular or delivery collection.
 *
 * @since import (0.0.1)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 * @global object $post The collection post object
 *
 * @param string $efpic_before_upload HTML content
 * @return string Filtered HTML content
 */
function efpic_import_dialog( $efpic_before_upload ) {
	global $post;

	// Get import folder
	$wp_upload_dir = wp_upload_dir();

	// Check if efpic folder exists, if not create it
	$efpic_folder = $wp_upload_dir['basedir'] . '/efpic';

	if ( ! is_dir( $efpic_folder ) ) {
		mkdir( $efpic_folder, 0755 );
	}

	// Check if import folder exists, if not create it
	$import_folder = $efpic_folder . '/import/';

	if ( ! is_dir( $import_folder ) ) {
		mkdir( $import_folder, 0755 );
	}

	// Get import folder content
	$files_temp = scandir( $import_folder );

	// Get rid of the dots
	$files_temp = array_diff( $files_temp, array( '..', '.' ) );

	// Get rid of everything but folders
	function efpic_import_folders_only( $possible_folder ) {
		$wp_upload_dir = wp_upload_dir();
		$import_folder = $wp_upload_dir['basedir'] . '/efpic/import/';
		return ( is_dir( $import_folder . $possible_folder ) ) ? true : false;
	}

	$folders = array_filter( $files_temp, 'efpic_import_folders_only' );

	// If there is nothing to import, tell the user
	if ( 0 >= count( $folders ) ) {
		return $efpic_before_upload . '<div class="efpic-folder-import"><p>' . __( 'Your import folder is empty. <a class="efpic-help" href="https://efpic.io/docs/pro/import/#how-to">Learn how to use it.</a>', 'efpic-pro' ) . '</p></div>';
	}

	// We found something to import...
	else {

		ob_start();
?>
		<div class="efpic-folder-import">
			<input type="hidden" id="efpic-import-post-id" name="efpic-import-post-id" value="<?php echo $post->ID; ?>" />
			<input type="hidden" id="efpic-import-ajax-url" name="efpic-import-ajax-url" value="<?php echo admin_url( 'admin-ajax.php' ); ?>" />
			<input type="hidden" id="efpic-import-nonce" name="efpic-import-nonce" value="<?php echo wp_create_nonce( 'efpic-import-files' ); ?>" />

<?php
			// Check if there are unfinished imports with this collection id
			$temporary_info = get_post_meta( $post->ID, '_efpic_import_temporary_id' );

			// If there are unfished imports and the folder still exsits, we say so
			if ( 0 < count( $temporary_info ) AND in_array( $temporary_info[0]['folder'], $folders ) ) {
				echo '<div class="efpic-temporary-import">';
				echo '<p>Import of the folder <strong>' . $temporary_info[0]['folder'] . '</strong> was interrupted. <a class="efpic-help" href="https://efpic.io/docs/pro/import/#resume-import">Learn more about how to resume a previous import</p><p>';

				// Sort array to get the last step number
				usort( $temporary_info, function( $a, $b ) {
					return $b['step'] - $a['step'];
				});

				// Show UI to resume or cancel the unfinished import
				echo '<a class="button button-primary js-resume-import" data-folder="'.$temporary_info[0]['folder'].'" data-step="' . ( (int) $temporary_info[0]['step'] + 1 ) . '" href="#">' . __( 'Resume Previous Import', 'efpic-pro' ) . '</a> <a class="button js-efpic-cancel-import" href="#">' . __( 'Cancel', 'efpic-pro' ). '</a></p></div>';

				// Hide regular import dialog if there is an unfinished import
				$temp = ' style="display: none;"';
			}
			elseif ( 0 < count( $temporary_info ) AND ! in_array( $temporary_info[0]['folder'], $folders ) ) {
				echo '<div class="efpic-temporary-import">';
				echo '<p>' . sprintf( __( 'A previous import was interrupted, but the folder <strong>%s</strong> doesn\'t exist any more. <a class="efpic-help" href="%s">Learn more about how to resume a previous import</a>', 'efpic-pro' ), $temporary_info[0]['folder'], 'https://efpic.io/docs/pro/import/#resume-import' ) . '</p>';
				echo '<p><a class="button js-efpic-cancel-import" href="#">' . __( 'Hm, Ok. Clean that mess up for me, please.', 'efpic-pro' ). '</a></p>';
				echo '</div>';
				$temp = ' style="display: none;"';
			}
			else {
				$temp = '';
			}
?>

				<div class="efpic-import-form"<?php echo $temp; ?>>
					<p><label for="efpic-import-folder"><?php _e( 'Import from folder', 'efpic-pro' ); ?>:</label>
						<select name="efpic-import-folder" id="efpic-import-folder">
							<option value=""><?php _e( 'Select', 'efpic-pro' ); ?></option>
					<?php
						foreach ( $folders as $folder ) {
							echo '<option value="' . $folder . '">' . $folder . '</option>';
						}
					?>
						</select>
						<input class="button js-efpic-import-images-form-folder" id="efpic-import-images-form-folder" name="efpic-import-images-form-folder" type="submit" value="<?php _e( 'Import Images', 'efpic-pro' ); ?>" />
					</p>
				</div><!-- .efpic-import-form -->
				<div class="or"><span><?php _e( 'or', 'efpic-pro' ); ?></span></div>
				<div class="efpic-import-status">
					<div class="efpic-importing"><strong><?php _e( 'Importing:', 'efpic-pro' ); ?></strong> <span></span></div>
					<div class="efpic-percentage">0%</div>
					<div class="efpic-import-progress-bar"><div></div></div>
				</div>
			</div><!-- .efpic-folder-import -->
<?php
		return $efpic_before_upload . ob_get_clean();
	}
}

add_filter( 'efpic_before_upload', 'efpic_import_dialog' );
add_filter( 'efpic_before_delivery_upload', 'efpic_import_dialog' );


/**
 * Change section header, add "Import".
 *
 * @since import (0.0.1)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 *
 * @param string $header The section headline
 * @return string The filtered headline 
 */
function efpic_import_change_section_header ( $header ) {
	$header = __( 'Import / Upload Images', 'efpic-pro' );
	return $header;
}

add_filter( 'efpic_section_header_1', 'efpic_import_change_section_header' );