<?php
/**
 * efpic Pro admin page (license & auto-updates removed).
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * Replace core add-ons page with a simple Pro admin page (reserved slots).
 */
function efpic_pro_register_admin_menu() {
	remove_submenu_page( 'efpic', 'efpic-add-ons' );

	add_submenu_page(
		'efpic',
		'efpic Pro',
		'efpic Pro',
		'manage_options',
		defined( 'EFPIC_PRO_LICENSE_PAGE' ) ? EFPIC_PRO_LICENSE_PAGE : 'efpic-pro',
		'efpic_pro_load_subpage'
	);
}

add_action( 'admin_menu', 'efpic_pro_register_admin_menu', 11 );

/**
 * Pro is always treated as licensed when the plugin is active.
 *
 * @return string
 */
function efpic_pro_get_license_status() {
	return 'valid';
}

/**
 * @return object
 */
function efpic_pro_get_license_data() {
	return (object) array( 'license' => 'valid' );
}

/**
 * Remove stored license data and external update checks.
 */
function efpic_pro_remove_licensing_data() {
	delete_option( 'efpic_pro_license_key' );
	delete_option( 'efpic_addon_licenses' );
	delete_transient( 'efpic_pro_license_status' );
}

add_action( 'init', 'efpic_pro_remove_licensing_data', 1 );

/**
 * Render the efpic Pro submenu page.
 */
function efpic_pro_load_subpage() {
	?>
	<div class="efpic-pro__head-wrapper">
		<?php
		if ( function_exists( 'efpic_render_ad_slot' ) ) {
			efpic_render_ad_slot( 'pro-page-header', 'efpic-pro__head-line' );
		}
		?>
	</div>
	<div class="wrap pro-page__wrap">
		<h2 style="display: none;"></h2>
		<?php
		if ( function_exists( 'efpic_render_ad_slot' ) ) {
			efpic_render_ad_slot( 'pro-page-main', 'efpic-pro-admin-slot' );
		}
		?>
	</div>
	<?php
}
