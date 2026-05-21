<?php
/**
 * Compatibility Fixes for plugin: Yoast SEO
 * 
 * @since 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Remove efpic collections from Yoast xml sitemaps
 *
 * @since 0.9.4
 */
function efpic_remove_from_wpseo_sitemap( $excluded, $post_type ) {
	if ( 'efpic_collection' == $post_type ) {
		return true;

	}

	return false;
}
add_filter( 'wpseo_sitemap_exclude_post_type', 'efpic_remove_from_wpseo_sitemap', 10, 2 );


/**
 * Remove efpic images from attachment sitemap
 *
 * @since 1.1.0
 */
function efpic_remove_attachments_from_yoast_sitemap( $output, $url ) {
	if ( isset( $url['images'][0]['src'] ) AND strpos( $url['images'][0]['src'], '/efpic/' ) ) {
		return '';
	}
	else {
		return $output;
	}

}
add_filter( 'wpseo_sitemap_url', 'efpic_remove_attachments_from_yoast_sitemap', 10, 2 );


/**
 * Remove efpic collections from Yoast accessible post types list (whatever that means)
 */
function efpic_remove_from_yoast( $post_types ) {
	unset( $post_types['efpic_collection'] );
	return $post_types;
}
add_filter( 'wpseo_accessible_post_types', 'efpic_remove_from_yoast' );


/**
 * Exclude collections from Yoast SEO indexable creation.
 *
 * @since 2.3.6
 *
 * @see https://developer.yoast.com/features/indexables/indexables-filters/#post_types
 *
 * @param array $excluded Array of excluded post types by name.
 * @return array The filtered post types.
 */
function efpic_do_not_create_indexables_for_collections( $excluded ) {
	$excluded[] = 'efpic_collection';
	return $excluded;
}

add_filter( 'wpseo_indexable_excluded_post_types', 'efpic_do_not_create_indexables_for_collections' );


// Enable for testing purposes only!
// add_filter('wpseo_enable_xml_sitemap_transient_caching', '__return_false');