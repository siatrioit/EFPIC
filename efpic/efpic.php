<?php
/**
 * Plugin Name: efpic
 * Plugin URI: https:www.edgarsfoto.lv
 * Description: Send a collection of photographs to your client for approval.
 * Version: 1.0.2
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Edgars
 * Author URI: https://www.edgarsfoto.lv
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: efpic
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Include functions for efpic
 *
 * @since 0.2.0
 */
if ( ! function_exists( 'efpic_setup' ) ) {

	function efpic_setup() {

		// Define plugin version
		define( 'EFPIC_VERSION', '1.0.2' );

		// Define path for this plugin
		define( 'EFPIC_PATH', plugin_dir_path(__FILE__) );

		// Define URL for this plugin
		define( 'EFPIC_URL', plugin_dir_url(__FILE__) );

		// Define efpic upload dir
		$upload_dir = wp_upload_dir();
		define( 'EFPIC_UPLOAD_DIR', $upload_dir['basedir'] . '/efpic' );

		// Include functions to render admin menu and settings page
		require EFPIC_PATH . 'backend/includes/efpic-settings.php';

		// Include functions to render add-ons page
		require EFPIC_PATH . 'backend/includes/efpic-addons-page.php';

		// Include function for registering custom post type collection
		require EFPIC_PATH . 'backend/includes/efpic-cpt-collection.php';

		// Include welcome screen
		require EFPIC_PATH . 'backend/includes/efpic-welcome-screen.php';

		// Include functions for admin notices and error messages
		require EFPIC_PATH . 'backend/includes/efpic-admin-notices.php';

		// Everything that doesn't fit anywhere else...
		require EFPIC_PATH . 'backend/includes/efpic-helper.php';

		// Include custom metabox and our custom edit screen
		require EFPIC_PATH . 'backend/includes/efpic-edit-collection.php';

		// Efpic media handling
		require EFPIC_PATH . 'backend/includes/efpic-media.php';

		// Handle front end ajax requests
		require EFPIC_PATH . 'frontend/includes/save-collection.php';

		// Handle backend ajax requests
		require EFPIC_PATH . 'backend/includes/efpic-ajax.php';

		// Fix compatibility issues with third parties
		require EFPIC_PATH . 'backend/includes/efpic-compatibility.php';
		
		// Efpic Email sending class
		require EFPIC_PATH . 'backend/includes/emails/class-efpic-emails.php';
		
		// Efpic Email sending functions
		require EFPIC_PATH . 'backend/includes/emails/efpic-emails.php';

		// Include template redirection etc. for collections
		require EFPIC_PATH . 'frontend/includes/efpic-template-functions.php';
		
		// Deprecated functions, filters and hooks
		require EFPIC_PATH . 'backend/includes/deprecated.php';

		// Disable telemetry (cleanup cron + DB)
		require EFPIC_PATH . 'backend/includes/efpic-telemetry.php';

		// Register efpic Blocks
		require EFPIC_PATH . 'blocks/efpic-blocks.php';

		// Autoload classes installed via composer (emogrify)
		require EFPIC_PATH . 'vendor/autoload.php';

		// Add efpic debug info to Site Health screen
		require EFPIC_PATH . 'backend/includes/efpic-site-health.php';

		// Empty ad slots + legacy Pro promo hooks (banners disabled)
		require EFPIC_PATH . 'backend/includes/efpic-ad-slots.php';
		require EFPIC_PATH . 'backend/includes/efpic-pro.php';

		// Check the settings version, run upgrader
		$settings_version = get_option( 'efpic_settings_version' );
		// Version upgrade is needed
		if ( empty( $settings_version ) ) {
			efpic_settings_upgrade();
		}
	}
}

add_action( 'after_setup_theme', 'efpic_setup' );


/**
 * Run upgrades after update
 *
 * Since 2.3.0
 */
function efpic_upgrade() {
	$settings_version = get_option( 'efpic_settings_version' );

	if ( version_compare( $settings_version, EFPIC_VERSION, '<' ) ) {
		efpic_collections_upgrade();
	}
}

// This needs to run after `init` so everything we need is in place!
add_action( 'init', 'efpic_upgrade', 11 );


/**
 * Set transient to display welcome screen on activation
 *
 * @since 0.7.0
 */
function efpic_activate_welcome_screen() {
	// Set transient for redirect to activation screen
	set_transient( '_efpic_welcome_screen_activation_redirect', true, 30 );
}

register_activation_hook( __FILE__, 'efpic_activate_welcome_screen' );


/**
 * Flush rewrite rules on plugin activation/deactivation
 *
 * @since 0.7.0
 */
function efpic_flush_rewrites() {

	// Include custom post type registration
	include( plugin_dir_path(__FILE__) . 'backend/includes/efpic-cpt-collection.php' );

	// Make sure our custom post types are defined first
	efpic_register_cpt_collection();

	// Flush the rewrite rules
	flush_rewrite_rules();

}

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'efpic_flush_rewrites' );


/**
 * Load some custom styling for our admin screens.
 *
 * @since 0.3.2
 */
function efpic_admin_styles_scripts() {

	global $post;

	$current_screen = get_current_screen();

	// Prevent conflicts in case no current_screen is set
	if ( empty( $current_screen ) ) {
		return;
	}

	// Only load those styles on edit collection screens
	if ( is_admin() ) {
		wp_enqueue_style( 'efpic-admin', EFPIC_URL . 'backend/css/efpic-admin.css', false, filemtime( EFPIC_PATH . 'backend/css/efpic-admin.css' ) );

		global $pagenow;
		if ( $current_screen->post_type == 'efpic_collection' AND get_post_type() == 'efpic_collection' AND $pagenow == 'post-new.php' || $pagenow == 'post.php' ) {
			// Enqueue wp.media scripts manually, because we don't load the default editor on collections
			// Post needs to be provided, or uploaded media will not get attached to the collection
			$args = array( 'post' => $post->ID );
			wp_enqueue_media( $args );
		}

		if ( $current_screen->base == 'efpic_page_efpic-design-appearance' ) {
			// Add the color picker css file
			wp_enqueue_style( 'wp-color-picker' );
		}

		// Make sure we are on the right screen
		if ( ( $current_screen->post_type == 'efpic_collection' && get_post_type() == 'efpic_collection' && $pagenow == 'post-new.php' || $pagenow == 'post.php' ) || ( $current_screen->base == 'dashboard_page_efpic-welcome-screen' ) || ( $current_screen->post_type == 'efpic_collection' AND get_post_type() == 'efpic_collection' AND $pagenow == 'edit.php' ) || strpos( $current_screen->base, 'efpic_page_efpic-' ) !== false ) {

			// Enqueue media
			wp_enqueue_media();

			// Enqueue script
			wp_enqueue_script( 'efpic-admin', EFPIC_URL . 'backend/js/efpic-admin.min.js', array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-sortable', 'underscore', 'backbone', 'wp-color-picker' ), filemtime( EFPIC_PATH . 'backend/js/efpic-admin.min.js' ), true );

			$post_id = false;
			if ( isset( $post->ID ) AND ! empty( $post->ID ) ) {
				$post_id = $post->ID;
			}

			// Localize it
			$efpic_localization_strings = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'efpic_ajax' ),
				'postID' => $post_id,
				'media_modal_title' => __( 'Upload Images', 'efpic' ),
				'media_modal_button_insert_text' => __( 'Insert Images', 'efpic' ),
				'sent_option_label' => __( 'Sent', 'efpic' ),
				'approved_option_label' => __( 'Approved', 'efpic' ),
				'expired_option_label' => __( 'Expired', 'efpic' ),
				'button_text_publish' => __( 'Publish', 'efpic' ),
				'button_text_send_to_client' => __( 'Send to Client', 'efpic' ),
				'selection_table_no_match' => __( 'No images found', 'efpic' ),
			);

			$efpic_localization_strings = apply_filters( 'efpic_localization_strings', $efpic_localization_strings );

			wp_localize_script( 'efpic-admin', 'efpic_admin', $efpic_localization_strings );
		}

	}

}

add_action( 'admin_enqueue_scripts', 'efpic_admin_styles_scripts' );


/**
 * Add settings link in plugins overview
 *
 * @param array $actions An array of links
 * @since 1.0.0
 */
function efpic_plugin_action_links( $actions ) {

	$action = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=efpic-settings' ), __( 'Settings', 'efpic' ) );
	array_unshift( $actions, $action );

	return $actions;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ) , 'efpic_plugin_action_links', 10 );


/**
 * Custom capability to manage efpic collections.
 *
 * Defaults to administrator privileges, can be filtered using 'efpic_capability'.
 *
 * @since 1.1.0
 */
function efpic_capability() {
	$efpic_capability = 'manage_options';
	$efpic_capability = apply_filters( 'efpic_capability', $efpic_capability );

	return $efpic_capability;
}


/**
 * Redirect /efpic to collection overview in WordPress admin
 *
 * @since 1.2.1
 */
function efpic_redirect_to_overview() {
	if ( $_SERVER['REQUEST_URI'] == '/efpic' OR $_SERVER['REQUEST_URI'] == '/efpic/' ) {
		wp_redirect( admin_url() . 'edit.php?post_type=efpic_collection' );
		exit;
	}
}

add_action( 'init', 'efpic_redirect_to_overview' );


/**
 * Check efpic Pro compatibility
 *
 * @since 2.0.0
 */
function efpic_check_pro_compat() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// Pro plugin is active in WP but setup did not finish (version mismatch, activation mode, etc.)
	if ( is_plugin_active( 'efpic-pro/efpic-pro.php' ) && ! defined( 'EFPIC_PRO_LOADED' ) ) {
		$notice = __( 'efpic Pro is activated but its modules did not load. Check admin notices on the Plugins screen or update both efpic and efpic Pro to matching versions.', 'efpic' );
		$notice_type = 'error';

		add_action(
			'admin_notices',
			function() use ( $notice, $notice_type ) {
				?>
				<div class="efpic-pro-module-notice notice notice-<?php echo esc_attr( $notice_type ); ?>">
					<p><?php echo esc_html( $notice ); ?></p>
				</div>
				<?php
			}
		);
	}
}

add_action( 'init', 'efpic_check_pro_compat' );


/**
 * Add efpic Pro upgrade box
 * 
 * @since 1.3.1
 */
function efpic_add_pro_metabox() {
	add_meta_box(
		'efpic-pro-metabox',
		'',
		'efpic_display_pro_metabox',
		'efpic_collection',
		'side',
		'high'
	);
}

add_action( 'add_meta_boxes', 'efpic_add_pro_metabox' );


/**
 * Render the Pro metabox.
 *
 * @since 1.3.1
 * @since 2.0.0 New box design and content
 */
function efpic_display_pro_metabox() {
	efpic_render_ad_slot( 'pro-metabox', 'efpic-pro-meta-box' );
}


/**
 * Hide admin bar for now
 */
add_filter( 'efpic_show_admin_bar', '__return_false' );


/**
 * Check if collection slug has changed
 * 
 * @since 1.5.0
 */
function efpic_check_collection_slug() {

	// Only run if collection or 404
	if ( 'efpic_collection' != get_post_type() AND ! is_404() ) {
		return;
	}

	// Only run, if pretty permalinks are active
	if ( ! get_option( 'permalink_structure' ) ) {
		return;
	}

	// Get current collection slug
	$post_type = get_post_type_object( 'efpic_collection' );
	$post_type_slug = $post_type->rewrite['slug'];

	// Get saved slug. Set, if empty
	$saved_slug = get_transient( 'efpic_collection_slug' );

	if ( empty( $saved_slug ) ) {
		set_transient( 'efpic_collection_slug', $post_type_slug, 0 );
		return;
	}
	
	// Compare current with saved collection slug
	if ( $post_type_slug != $saved_slug ) {
		$old_efpic_slugs = get_transient( 'efpic_collection_old_slugs' );
		if ( ! is_array( $old_efpic_slugs ) ) {
			$old_efpic_slugs = [];	
		}
		$old_efpic_slugs[] = $saved_slug;
		set_transient( 'efpic_collection_old_slugs', array_unique( $old_efpic_slugs ) );
		set_transient( 'efpic_collection_slug', $post_type_slug, 0 );
		flush_rewrite_rules( false );
	}
}

add_action( 'wp', 'efpic_check_collection_slug' );


/**
 * Redirect when old collection base slug is used
 * 
 * @since 1.5.0
 */
function efpic_redirect_from_old_slug() {

	// Only run, if this is a 404
	if ( ! is_404() ) {
		return;
	}

	// Only run, if pretty permalinks are active
	if ( ! get_option( 'permalink_structure' ) ) {
		return;
	}

	// Check if the old slug transient is set
	$old_efpic_slugs = get_transient( 'efpic_collection_old_slugs' );

	if ( empty( $old_efpic_slugs ) ) {
		return;
	}

	// Get current url & path
	global $wp;
	$current_url = home_url( $wp->request );
	$url = parse_url( $current_url );
	$path = '';
	if ( ! empty( $url['path'] ) ) {
		$path = explode( '/', $url['path'] );
	}

	// Get current efpic collection slug
	$post_type_object = get_post_type_object( 'efpic_collection' );
	$current_slug = $post_type_object->rewrite['slug'];

	if ( empty( $path[1] ) OR ! in_array( $path[1], $old_efpic_slugs ) OR $path[1] == $current_slug ) {
		return;
	}
	
	// Replace old slug with the new one
	$new_url = $url['scheme'] . '://' . $url['host'] . '/' . $current_slug . '/' . $path[2] . '/';

	// Get post type by url for the collection
	$post_type = get_post_type( url_to_postid( $new_url ) );

	// Redirect if the url is actually a collection
	if ( 'efpic_collection' == $post_type ) {
		wp_redirect( trailingslashit( $new_url ), 301 );
	}
}

add_filter( 'wp', 'efpic_redirect_from_old_slug' );


/**
 * Initialize proof file download
 * 
 * @since 1.5.0
 */
function efpic_trigger_proof_file_download() {
	if ( current_user_can( efpic_capability() ) && ! empty( $_REQUEST['efpic-download'] ) && $_REQUEST['efpic-download'] == 'efpic-proof-file' ) {
		efpic_create_proof_file( $_REQUEST['post'] );
		exit;
	}
}

add_action( 'init', 'efpic_trigger_proof_file_download' );


/**
 * Display Pro hint between image upload and sharing options
 * 
 * @since 1.6.0
 */
function efpic_display_pro_hint() {
	efpic_render_ad_slot( 'pro-hint', 'efpic-pro-hint' );
}


/**
 * Determine if Pro is active and license is valid.
 *
 * @since 1.6.0
 * @since 1.9.0 Use function from Pro plugin, also checking license status
 * @since 2.0.1 No longer checking license status
 *
 * @return bool Whether Pro is active or not
 */
function efpic_is_pro_active() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! is_plugin_active( 'efpic-pro/efpic-pro.php' ) ) {
		return false;
	}

	return defined( 'EFPIC_PRO_LOADED' ) && EFPIC_PRO_LOADED;
}


/**
 * Pro license checks are disabled; Pro features follow plugin activation.
 *
 * @since 2.0.1
 *
 * @return bool Whether Pro features should be available
 */
function efpic_is_pro_license_valid() {
	return efpic_is_pro_active();
}


/**
 * Never use "private" post status.
 *
 * @since 2.3.3
 *
 * @param array $data An array of slashed, sanitized, and processed post data
 * @return array Filtered post data
 */
function efpic_remove_post_status_private( $data ) {
	if ( $data['post_type'] == 'efpic_collection' && $data['post_status'] == 'private' ) {
		$data['post_status'] = 'approved';
	}

	return $data;
}

add_action( 'wp_insert_post_data', 'efpic_remove_post_status_private', 10 );


/**
 * Protect efpic folders from browsing.
 *
 * This will disable browsing the folder even if directory listing
 * is active by adding an index.php to existing collection folders.
 *
 * @since 2.5.4
 */
function efpic_protect_folders_from_browsing() {
	// Get all upload folders
	$efpic_folders = array_filter( glob( EFPIC_UPLOAD_DIR . '/collections/*' ), 'is_dir' );

	// Iterate through folders and add index.php
	foreach( $efpic_folders as $upload_path ) {
		efpic_add_folder_index( $upload_path );
	}

	// Allow Pro to hook into this action
	do_action( 'efpic_protect_folders', $efpic_folders );
}


/**
 * Put index.php file in a folder.
 *
 * @since 2.5.4
 *
 * @param string $path The folder path.
 */
function efpic_add_folder_index( $path ) {
	// Set path to index file
	$index_file = trailingslashit( $path ) . 'index.php';

	// Check whether the file already exists
	if ( ! file_exists( $index_file ) ) {
		// Place index.php file
		if ( is_writable( $path ) ) {
			$index_content = "<?php\n// Silence is golden.";
			file_put_contents( $index_file, $index_content );
		}
		else {
			error_log( sprintf( __( 'Unable to protect folder: %s', 'efpic' ), print_r( $path, true ) ) );
		}
	}
}


/**
 * Schedule collection folder check.
 *
 * @since 2.5.4
 */
if ( ! wp_next_scheduled( 'efpic_collection_folders' ) ) {
	wp_schedule_event( time(), 'daily', 'efpic_collection_folders' );
}


/**
 * Add action to check collection folders.
 *
 * @since 2.5.4
 */
add_action( 'efpic_collection_folders', 'efpic_protect_folders_from_browsing' );
