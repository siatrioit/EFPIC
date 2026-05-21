<?php
/**
 * Adding debug info to Site Health Screen
 *
 * @since 1.7.8
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function efpic_add_debug_info( $debug_info ) {

	// $licenses = get_option( 'efpic_addon_licenses' );
	// $license = efpic_get_license_info( $licenses['efpic_pro'], 'efpic Pro' );

	$efpic_collection_cpt = get_post_type_object( 'efpic_collection' );
	$efpic_collection_slug = $efpic_collection_cpt->rewrite['slug'];

	$php_extensions = get_loaded_extensions();
	sort( $php_extensions );

	$debug_info['efpic'] = array(
		'label'    => __( 'efpic', 'efpic' ),
		'fields'   => array(
			// 'license' => array(
			// 	'label'    => 'License',
			// 	'value'   => $license->license,
			// 	'private' => true,
			// ),
			'efpic_upload_dir' => [
				'label' => 'efpic upload directory',
				'value' => EFPIC_UPLOAD_DIR
			],
			'efpic_base_slug' => [
				'label' => 'efpic base slug',
				'value' => $efpic_collection_slug
			],
			'safe_mode' => [
				'label' => 'Safe mode',
				'value' => ini_get( 'safe_mode' ) ? 'On' : 'Off'
			],
			'server_time' => [
				'label' => 'Server time',
				'value' => esc_html( date( 'H:i' ) ),
			],
			'blog_time' => [
				'label' => 'Blog time',
				'value' => wp_date( 'H:i', time() )
			],
			'memory_in_use' => [
				'label' => 'Memory in use',
				'value' => size_format( @memory_get_usage( TRUE ), 2 )
			],
			'php_extensions' => [
				'label' => 'Loaded PHP extensions',
				'value' => esc_html( implode( ', ', $php_extensions ) )
			]
		),
	);

	return $debug_info;
}
add_filter( 'debug_information', 'efpic_add_debug_info' );