<?php
/**
 * Plugin Name: efpic Pro
 * Description: Professional photo proofing features for photographers.
 * Plugin URI: https://www.edgarsfoto.lv
 * Version: 1.0.6
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: efpic
 * Author: Edgars
 * Author URI: https://www.edgarsfoto.lv
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text-Domain: efpic-pro
 */
defined( 'ABSPATH' ) OR exit;

/**
 * Initiate "activation mode" if at least one of the old Pro modules is active.
 *
 * @since 1.0.0
 */
function efpic_pro_activation() {
	// Nodrošinām, ka WordPress spraudņu funkcijas ir pieejamas
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$activate_activation_mode = false;

	foreach( efpic_get_old_pro_modules() as $pro_module ) {
		if ( is_plugin_active( $pro_module ) ) {
			$activate_activation_mode = true;
		}
	}

	if ( $activate_activation_mode ) {
		add_option( '_efpic_pro_activation_mode', true, '', false );
	}
}

register_activation_hook( __FILE__, 'efpic_pro_activation' );


/**
 * Include functions for efpic.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'efpic_pro_setup' ) ) {

	function efpic_pro_setup() {
		// Define plugin version
		define( 'EFPIC_PRO', '1.0.6' );

		define( 'EFPIC_PRO_NAME', 'efpic Pro' );
		define( 'EFPIC_PRO_LICENSE_PAGE', 'efpic-pro' );

		// Define path for this plugin
		define( 'EFPIC_PRO_PATH', plugin_dir_path( __FILE__ ) );

		// Define URL for this plugin
		define( 'EFPIC_PRO_URL', plugin_dir_url( __FILE__ ) );

		// Define plugin basename
		define( 'EFPIC_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

		// Minimum efpic (core) version — aligned with custom 1.x release line
		define( 'EFPIC_CORE_REQUIRED', '1.0.0' );

		// Define minimumPHP version
		define( 'EFPIC_PHP_REQUIRED', '7.4' );

		// Check for dependencies
		if ( ! efpic_pro_check_php_version() ) {
			return;
		}

		if ( ! efpic_pro_check_efpic_core_version() ) {
			return;
		}

		// Pro admin page (no license / no remote updates)
		require_once EFPIC_PRO_PATH . 'inc/pro-page.php';

		// Clear stale activation mode when no legacy Pro add-ons are active
		if ( get_option( '_efpic_pro_activation_mode' ) && ! efpic_pro_has_active_old_modules() ) {
			delete_option( '_efpic_pro_activation_mode' );
		}

		// Handle plugin activation
		$activation_mode = get_option( '_efpic_pro_activation_mode' );
		if ( $activation_mode ) {
			require_once( plugin_dir_path( __FILE__ ) . 'inc/activation.php' );
			return;
		}

		// Include helper functions
		require_once EFPIC_PRO_PATH . 'inc/pro-helper.php';

		// Include settings
		require_once EFPIC_PRO_PATH . 'inc/settings.php';

		// Include enhanced expiration
		require_once EFPIC_PRO_PATH . 'inc/collection-expiration.php';

		// Client registration
		require_once EFPIC_PRO_PATH . 'inc/client-registration.php';

		// Include Brand & Customize
		require_once EFPIC_PRO_PATH . 'legacy/efpic-brand-customize/efpic-brand-customize.php';

		// Include Mark & Comment
		require_once EFPIC_PRO_PATH . 'legacy/efpic-mark-comment/efpic-mark-comment.php';

 		// Include Selection Options
		require_once EFPIC_PRO_PATH . 'legacy/efpic-selection-options/efpic-selection-options.php';

		// Include Download
		require_once EFPIC_PRO_PATH . 'legacy/efpic-download/efpic-download.php';

		// Include Import
		require_once EFPIC_PRO_PATH . 'legacy/efpic-import/efpic-import.php';

		// Theft Protection
		require_once EFPIC_PRO_PATH . 'legacy/efpic-theft-protection/efpic-theft-protection.php';

		// Delivery
		require_once EFPIC_PRO_PATH . 'legacy/efpic-delivery/efpic-delivery.php';

		// Include traduttore registry
		if ( ! function_exists( '\efpic_Pro\Traduttore_Registry\add_project' ) ) {
			require_once EFPIC_PRO_PATH . 'inc/traduttore-registry.php';
		}

		\efpic_Pro\Traduttore_Registry\add_project(
			'plugin',
			'efpic-pro',
			'https://translate.efpic.io/api/translations/efpic-pro/'
		);

		define( 'EFPIC_PRO_LOADED', true );
	}

	// Mainām ielādes āķi uz plugins_loaded un iedodam prioritāti 20, 
	// lai tas vienmēr ielādējas aiz pamata spraudņa (kam ir prioritāte 10)
	add_action( 'plugins_loaded', 'efpic_pro_setup', 20 );
}


/**
 * Return old Pro module paths.
 *
 * @since 1.0.0
 * * @return array Old Pro module paths
 */
/**
 * Whether any legacy standalone Pro add-on plugin is still active.
 *
 * @return bool
 */
function efpic_pro_has_active_old_modules() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	foreach ( efpic_get_old_pro_modules() as $pro_module ) {
		if ( is_plugin_active( $pro_module ) ) {
			return true;
		}
	}

	return false;
}


function efpic_get_old_pro_modules() {
	return [
		'efpic-brand-customize/efpic-brand-customize.php',
		'efpic-delivery/efpic-delivery.php',
		'efpic-download/efpic-download.php',
		'efpic-import/efpic-import.php',
		'efpic-mark-comment/efpic-mark-comment.php',
		'efpic-selection-options/efpic-selection-options.php',
		'efpic-theft-protection/efpic-theft-protection.php'
	];
}


/**
 * Check for old Pro modules
 *
 * @since 1.0.0
 */
function efpic_pro_check_old_pro_modules() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$activate_activation_mode = false;

	// Check if one of the old Pro modules is active
	foreach( efpic_get_old_pro_modules() as $pro_module ) {
		if ( is_plugin_active( $pro_module ) ) {
			$activate_activation_mode = true;
		}
	}

	if ( $activate_activation_mode ) {
		add_option( '_efpic_pro_activation_mode', true, '', false );
	}
}

add_action( 'admin_init', 'efpic_pro_check_old_pro_modules', 1 );


/**
 * Check if PHP version is adequate
 * * @since 1.0.0
 * * @return bool Whether the version is adequate
 */
function efpic_pro_check_php_version() {
	if ( defined( 'PHP_VERSION' ) AND version_compare( PHP_VERSION, EFPIC_PHP_REQUIRED ) >= 0 ) {
		return true;
	}

	add_filter( 'admin_notices', function() {
		?>
		<div class="notice notice-error">
			<p>🚨 <?php echo sprintf( __( 'To use efpic Pro, you need at least PHP version %1$s. %2$sLearn more%3$s', 'efpic-pro' ), EFPIC_PHP_REQUIRED, '<a href="https://efpic.io/docs/pro#requirements">', '</a>' ); ?></p>
		</div>
		<?php
	});
	
	return false;
}


/**
 * Check if efpic Core version is adequate
 * * @since 1.0.0
 * * @return bool Whether the version is adequate
 */
function efpic_pro_check_efpic_core_version() {
	if ( defined( 'EFPIC_VERSION' ) AND version_compare( EFPIC_VERSION, EFPIC_CORE_REQUIRED ) >= 0 ) {
		return true;
	}

	add_filter( 'admin_notices', function() {
		?>
		<div class="notice notice-error">
			<p>🚨 <?php echo sprintf( __( 'To use this version of efpic Pro, you need at least version %1$s of efpic. %2$sInstall or update now%3$s', 'efpic-pro' ), EFPIC_CORE_REQUIRED, '<a href="' . admin_url( 'plugin-install.php?s=efpic%2520photo%2520proofing&tab=search&type=term' ) . '">', '</a>' ); ?></p>
		</div>
		<?php
	});
	
	return false;
}


/**
 * Enqueue styles.
 * * @since 1.0.0
 */
function efpic_pro_admin_styles_scripts() {
	wp_enqueue_style( 'efpic-pro-admin', EFPIC_PRO_URL . 'assets/css/efpic-pro-admin.css', false, filemtime( EFPIC_PRO_PATH . 'assets/css/efpic-pro-admin.css' ) );
}

add_action( 'admin_enqueue_scripts', 'efpic_pro_admin_styles_scripts' );


/**
 * Allow access to collections by default.
 *
 * @since 1.4.0
 */
add_filter( 'efpic_collection_bouncer', '__return_true' );


/**
 * Prevent efpic from creating a default client
 *
 * This will prevent efpic from creating a default client
 * when publishing a new collection.
 *
 * @since 1.4.0
 */
add_filter( 'efpic_create_default_client', '__return_false' );