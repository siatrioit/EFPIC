<?php
/**
 * Reserved banner areas (promotional content removed).
 *
 * @since 3.3.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Empty slot above settings and on collection screens (legacy BF banner position).
 */
function efpic_maybe_show_bf_banner() {
	efpic_render_ad_slot( 'bf-banner', 'efpic-bf-banner' );
}

add_action( 'efpic_pre_settings', 'efpic_maybe_show_bf_banner', 9 );

/**
 * Empty slot on collection list and edit screens.
 */
function efpic_bf_admin_notices() {
	$screen = get_current_screen();

	if ( empty( $screen->id ) || ! in_array( $screen->id, array( 'edit-efpic_collection', 'efpic_collection' ), true ) ) {
		return;
	}

	efpic_maybe_show_bf_banner();
}

add_action( 'admin_notices', 'efpic_bf_admin_notices' );
