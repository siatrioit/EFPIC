<?php
/**
 * Compatibility Fixes for plugin: Jetpack
 * 
 * @since 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Do not use Jetpacks photon for efpic images
 *
 * @since 1.4.5
 */
function efpic_no_photon() {
	if ( get_post_type() == 'efpic_collection' ) {
		add_filter( 'jetpack_photon_skip_image', '__return_true' );
	}
}
add_action( 'wp', 'efpic_no_photon' );


/**
 * Do not use Jetpack's Photon for efpic images (Part II).
 *
 * @see https://developer.jetpack.com/hooks/jetpack_photon_skip_for_url/
 *
 * @param bool $skip Whether Photon should ignore that image
 * @param string $image_url The image URL
 */
function efpic_photon_skip_image( $skip, $image_url ) {
	// Check if it is a efpic image
	if ( strpos( '/efpic/collections/', $image_url ) ) {
		return true;
	}
	
	return $skip;
}
add_filter( 'jetpack_photon_skip_for_url', 'efpic_photon_skip_image', 10, 2 );