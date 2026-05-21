<?php
/**
 * Disable right clicks
 *
 * @since theft-protection (0.0.2)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Disable right clicks on efpic collections.
 *
 * @since theft-protection (0.0.2)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param string $scripts The script code
 * @return string The filtered scripts code
 */
function efpic_disable_right_click( $custom_scripts ) {
	if ( get_option( 'efpic_disable_right_click' ) == 'on' ) {
		$custom_scripts .= '<script>document.addEventListener( \'contextmenu\', event => event.preventDefault() );</script>';
		return $custom_scripts;
	}

	return $custom_scripts;
}

add_filter( 'efpic_custom_scripts', 'efpic_disable_right_click' );


/**
 * Disable hold and save on touch devices.
 *
 * @since theft-protection (0.6.0)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param string $styles Custom CSS
 * @return string The filtered CSS
 */
function efpic_theft_protection_disable_user_select( $styles ) {
	$styles .= '.efpic-imgbox-inner, .efpic-lightbox-image-container { -webkit-user-select: none; -moz-user-select: none; user-select: none; }
	.efpic-imgbox-inner img, .efpic-lightbox-image-container img { -webkit-touch-callout: none; }';

	return $styles;
}

add_filter( 'efpic_custom_styles', 'efpic_theft_protection_disable_user_select' );