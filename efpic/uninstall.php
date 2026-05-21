<?php
/**
 * Uninstall routines
 *
 * @since 0.5.0
 */

// Prevent malicious or accidental use of this file
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

// Load efpic media handling to acces functions from there
require 'backend/includes/efpic-media.php';

/**
 * Delete all collections and the corresponding images
 *
 * @since 0.5.0
 */

function efpic_delete_collections_and_images() {
	// Define what we want to load (all efpic collections)
	$args = array(
		'post_type' => 'efpic_collection',
		'posts_per_page' => -1,
		'post_status' => 'any'
	);

	// Load an array of collections
	$collections = get_posts( $args );

	// Make sure we actually got something back
	if ( is_array( $collections ) ) {
		// Loop through all collections
		foreach ( $collections as $collection ) {

			// Remove associated attachments for each collection
			efpic_delete_attached_media( $collection->ID );

			// Delete all other post data for each collection
			wp_delete_post( $collection->ID, true );

		}
	}
}


/**
 * Uninstall routine
 *
 * @since 0.5.0
 */

// Check if we are on a Multisite installation
if ( is_multisite() )  {

	// Load blog ids into a variable
	global $wpdb;
	$sites = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );

	// Check if there are any sites in this Multisite Installation
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog($site['blog_id']);

			// Delete all collections and images
			efpic_delete_collections_and_images();

			// Delete our settings from the database
			delete_option( 'efpic_settings' );

		}
		restore_current_blog();
	}

} else {

	// Delete all collections and images
	efpic_delete_collections_and_images();

	// Delete our settings from the database
	delete_option( 'efpic_settings' );

}
