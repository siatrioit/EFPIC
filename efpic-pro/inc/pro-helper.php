<?php
/**
 * efpic Pro helper functions
 *
 * @since 1.4.5
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Adjust view link in the Admin Bar.
 *
 * @since 1.4.5
 *
 * @param object $admin_bar The admin bar.
 */
function efpic_pro_admin_bar_view_collection( WP_Admin_Bar $admin_bar ) {
	// Only run this in the Admin
	if ( ! is_admin() ) {
		return;
	}
	
	// Get more info about the collection
	global $post;

	// Only run this when editing a efpic collection
	$current_screen = get_current_screen();
	if ( empty( $current_screen->id ) || ( ! empty( $current_screen->id ) && $current_screen->id != 'efpic_collection' ) || $post->post_status == 'delivery-draft' || $post->post_status == 'delivered' ) {
		return;
	}

	// Exit, if collection is still a draft
	if ( in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) ) ) {
		return;
	}
	
	// If there is more than one client, add dropdown links for all clients
	$hashes = get_post_meta( $post->ID, '_efpic_collection_hashes', true );

	if ( ! empty( $hashes ) && count( $hashes ) >= 1 ) {
		foreach( $hashes as $ident => $client ) {
			$admin_bar->add_menu(
				[
					'parent' => 'view',
					'title' => sprintf( __( 'View as %s', 'efpic-pro' ), efpic_combine_name_email( $client['name'], $client['email'] ) ),
					'id' => $ident,
					'href' => esc_url( add_query_arg( 'ident', $ident, get_the_permalink() ) ),
				]
			);
		}
	}
}

add_action( 'admin_bar_menu', 'efpic_pro_admin_bar_view_collection', 500 );