<?php
/**
 * Client gallery text filter (filename + IPTC).
 *
 * @since 1.0.21
 */
defined( 'ABSPATH' ) || exit;

/**
 * Add searchText (+ IPTC fields) to each gallery image payload.
 *
 * @param array $current_image Image data.
 * @return array
 */
function efpic_text_filter_image_data( $current_image ) {
	if ( empty( $current_image['imageID'] ) ) {
		return $current_image;
	}

	$iptc = efpic_get_attachment_iptc( $current_image['imageID'] );
	$current_image['iptcTitle']       = $iptc['title'];
	$current_image['iptcDescription'] = $iptc['description'];
	$current_image['iptcKeywords']    = $iptc['keywords'];
	$current_image['searchText']      = efpic_build_image_search_text( $current_image['imageID'], $current_image );

	return $current_image;
}
add_filter( 'efpic_single_image_data', 'efpic_text_filter_image_data', 30 );
