<?php
/**
 * Compatibility Fixes for plugin: Real Media Library
 * 
 * @since 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Deactivate Real Media Library for efpic collections
 *
 * @since 1.3.5
 */
function efpic_deactivate_real_media_library( $active ) {

	global $post;

	if ( isset( $post->post_type ) AND $post->post_type == 'efpic_collection' ) {
		$active = false;
	}

	return $active;

}
add_filter( 'RML/Active', 'efpic_deactivate_real_media_library' );