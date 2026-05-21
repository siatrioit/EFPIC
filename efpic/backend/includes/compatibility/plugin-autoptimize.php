<?php
/**
 * Compatibility Fixes for plugin: Autoptimize
 * 
 * @since 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Prevent Autoptimize from "optimizing" aka breaking stuff
 *
 * @since 1.3.2
 */
function efpic_no_autoptimize() {
	global $post;

	if ( isset( $post->post_type ) && $post->post_type == 'efpic_collection' ) {
		return true;
	}

	return false;
}
add_filter( 'autoptimize_filter_noptimize', 'efpic_no_autoptimize' );