<?php
/**
 * Customizing the collection
 *
 * @since brand-customize (0.0.1)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Replace default image title.
 *
 * @since brand-customize (0.1.1)
 * 
 * @see efpic/frontend/includes/efpic-template-functions.php
 *
 * @param array $current_image Image data
 * @return array The filtered image
 */
function efpic_bc_image_title( $current_image ) {
	$file_name_comp = get_option( 'efpic_file_name_comp' );

	$meta = wp_get_attachment_metadata( $current_image['imageID'], true );

	if ( $meta ) {
		$file = pathinfo( $meta['file'] );

		$title = array();

		foreach( explode( ' ', $file_name_comp ) as $part ) {
			$pattern = substr( $part, 1, -1 );

			if ( 'number' == $pattern ) {
				$title['number'] = $current_image['number'];
			}
			elseif ( 'filename' == $pattern ) {
				$title['filename']= $file['filename'];
			}
			elseif ( 'file_extension' == $pattern ) {
				$title['extenstion'] = $file['extension'];
			}
			elseif ( isset( $meta['image_meta'][$pattern] ) AND ! empty( $meta['image_meta'][$pattern] ) ) {
				if ( 'shutter_speed' == $pattern ) {
					if ( (1 / $meta['image_meta']['shutter_speed'] ) > 1) {
						$title['shutter-speed'] = '1/';
						if (number_format( (1 / $meta['image_meta']['shutter_speed'] ), 1 ) ==  number_format( (1 / $meta['image_meta']['shutter_speed'] ), 0) ) {
							$title['shutter-speed'] .= number_format( (1 / $meta['image_meta']['shutter_speed']), 0, '.', '' ) . ' sec';
						} else {
							$title['shutter-speed'] .= number_format((1 / $meta['image_meta']['shutter_speed']), 1, '.', '') . ' sec';
						}
					} else {
						$title['shutter-speed'] = $meta['image_meta']['shutter_speed'] . ' sec';
					}

				}
				elseif ( 'aperture' == $pattern ) {
					$title['aperture'] = 'F' . $meta['image_meta']['aperture'];
				}
				elseif ( 'focal_length' == $pattern ) {
					$title['focal-length'] = $meta['image_meta']['focal_length'] . 'mm';
				}
				elseif ( 'iso' == $pattern ) {
					$title['iso'] = 'ISO ' . $meta['image_meta']['iso'];
				}
				else {
					$title[$pattern] = $meta['image_meta'][$pattern];
				}
			}
		}

		$current_image['title'] = $title;
	}

	return $current_image;
}

add_filter ( 'efpic_single_image_data', 'efpic_bc_image_title' );


/**
 * Add body class for image size switching.
 *
 * @since brand-customize (0.0.1)
 * 
 * @param array $body_classes The body classes
 * @return array The filtered classes
 */
function efpic_bc_add_image_size_body_class( $body_classes ) {
	$body_classes[] = 'thumbsize-' . get_option( 'efpic_image_size' );

	return $body_classes;
}

add_filter( 'efpic_body_classes', 'efpic_bc_add_image_size_body_class');


/**
 * Echo dynamic styles.
 *
 * @since brand-customize (0.1.0)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param string $custom_stlyes The custom styles
 * @return string The filtered styles
 */
function efpic_bc_custom_styles( $custom_styles ) {
	// Add color styles
	if ( ! empty( get_option( 'efpic_primary_color' ) ) ) {

		$color = get_option( 'efpic_primary_color' );

		$color_hsl = efpic_pro_hex2hsl( $color );
		$color_rgb = efpic_pro_hsl2rgb( $color_hsl );
		$color_hex = efpic_pro_rgb2hex( $color_rgb );

		$text_color = efpic_pro_get_contrast_color( $color_hex );

		// Make dark color:
		if ( get_option( 'efpic_theme' ) == 'light' ) {
			$color_hsl[2] = $color_hsl[2] * 1.7;
			$color_rgb_variant = efpic_pro_hsl2rgb( $color_hsl );
		}
		else {
			$color_hsl[2] = $color_hsl[2] / 3;
			$color_rgb_variant = efpic_pro_hsl2rgb( $color_hsl );
		}

		$color_variant = 'rgba( ' . $color_rgb_variant[0] * 255 . ', ' . $color_rgb_variant[1] * 255 . ', ' . $color_rgb_variant[2] * 255 . ', 75% )';

		$color_semi_transparent = 'rgba( ' . $color_rgb[0] * 255 . ', ' . $color_rgb[1] * 255 . ', ' . $color_rgb[2] * 255 . ', 80% )';

		// Add to exisiting styles
		$custom_styles .= '
			.efpic-gallery-item.selected .efpic-figure {
				border: 2px solid ' . $color . ';
			}
			.efpic-gallery-item.selected .efpic-figure .efpic-caption label svg {
				fill: ' . $color . ';
			}	
			@media (hover) {
				.efpic-gallery-item input[type=checkbox] + label:hover {
					background-color: ' . $color . ';
				}
			}
			.efpic-button.primary {
				color: ' . $text_color . ';
				background-color: ' . $color . ';
				border: 1px solid rgba(0,0,0,0.25);
				border-bottom: 3px solid rgba(0,0,0,0.5);
			}
			@media (hover) {
				.efpic-button.primary:hover {
					color: ' . $text_color . ';
					background-color: ' . $color . ';
					border: 1px solid rgba(0,0,0,0.25);
					border-bottom: 3px solid rgba(0,0,0,0.5);
				}
			}
			.efpic-lightbox-image-container img.selected,
			.efpic-lightbox-inner.mark-comment .efpic-lightbox-image-container img.selected {
				border: 5px solid ' . $color . ';
			}
			.efpic-lightbox-navigation .efpic-lightbox-select.selected svg {
				fill: ' . $color . ';
			}
			@media (hover) {
				.efpic-lightbox-navigation a:hover {
					background-color: ' . $color . ';
				}
			}
			.efpic-comment-marker {
				border-color: ' . $color . ';
			}

			.is-focused .efpic-comment-marker,
			.efpic-comment:hover .efpic-comment-marker,
			.editable .efpic-comment-marker {
				background-color: ' . $color_variant . ';
			}

			.is-focused .efpic-comment-marker:before,
			.efpic-comment:hover .efpic-comment-marker:before,
			.editable .efpic-comment-marker:before {
				background-color: ' . $color . ';
			}

			.marker {
				border-color: ' . $color . ';
				box-shadow:
					0 0 6px rgba( 0, 0, 0, 0.5 ),
					0 0 0 0 ' . $color_semi_transparent . ',
					0 0 0 0 ' . $color_variant . ';
			}

			.marker:hover,
			.marker.is-focused {
				background-color: ' . $color_variant . ';
			}

			.marker.is-active {
				background-color: ' . $color_variant . ';
			}

			.marker:hover:before,
			.marker.is-active:before,
			.marker.is-focused:before {
				background-color: ' . $color . ';
			}
		';
	}

	// Add font-family styles
	$font_options = get_option( 'efpic_font' );
	if ( ! empty( $font_options ) ) {
		if ( ! empty( $font_options['method'] ) AND $font_options['method'] == 'efpic-external-font' AND ! empty( $font_options['external_font_name'] ) ) {
			$font = $font_options['external_font_name'];
		}
		else {
			$font = $font_options['font'];
		}

		$custom_styles .= '
			body {
				font-family: ' . $font . ';
			}
		';
	}

	$custom_styles = apply_filters( 'efpic_brand_customize_styles', $custom_styles );

	return $custom_styles;
}

add_filter( 'efpic_custom_styles', 'efpic_bc_custom_styles', 10, 1 );


/**
 * Adjust email styles.
 *
 * @since 1.0.2
 *
 * @param array $styles The email styles
 * @return array The adjusted email styles
 */
add_filter( 'efpic_mail_styles', function( $styles ) {
	$primary_color = apply_filters( 'efpic_primary_color', '#7ad03a', 11 );
	$styles['button_text'] = efpic_pro_get_contrast_color( $primary_color );
	return $styles;
} );


/**
 * Adjust primary color.
 *
 * @since brand-customize (0.0.7)
 *
 * @see efpic/templates/emails/email-styles.php
 *
 * @param string $primary_color The primary color
 * @return string The filtered color
 */
function efpic_bc_adjust_primary_color( $primary_color ) {
	if ( ! empty( get_option( 'efpic_primary_color' ) ) ) {
		$primary_color = htmlspecialchars( get_option( 'efpic_primary_color' ) );
	}

	return $primary_color;
}

add_filter( 'efpic_primary_color', 'efpic_bc_adjust_primary_color', 10, 1 );


/**
 * Add custom styleseehts to the frontend.
 *
 * @since brand-customize (0.0.6)
 *
 * @see efpic/frontend/includes/efpic-template-functions.php
 *
 * @param string $styles_output The header style/link declarations 
 * @return string The filtered declarations
 */
function efpic_bc_custom_styles_output( $styles_output ) {
	$custom_styles_output = $styles_output . "\n";
	$font = get_option( 'efpic_font' );

	// Check font method
	if ( ! empty( $font['method'] ) AND $font['method'] == 'efpic-external-font' AND ! empty( $font['external_font_vendor'] ) ) {

		// Using Typekit
		if ( $font['external_font_vendor'] == 'typekit' ) {
			$typekit = '<link rel="stylesheet" href="https://use.typekit.net/' . $font['external_font_kit_id'] . '.css" />';
			$custom_styles_output = $custom_styles_output . $typekit;
		}

		// Using Google Fonts
		elseif ( $font['external_font_vendor'] == 'google' ) {
			echo '<link rel="preconnect" href="https://fonts.gstatic.com">';
			echo "\n\t\t";
			echo '<link href="https://fonts.googleapis.com/css?family=' . $font['external_font_family_parameter'] .'" rel="stylesheet">';
		}
	}

	return $custom_styles_output;
}

add_filter( 'efpic_styles_output', 'efpic_bc_custom_styles_output', 10, 1 );


/**
 * Echo custom header elements.
 *
 * @since brand-customize (0.1.0)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param string $custom_header The header HTML
 * @param int $id The collection ID
 * @return string The filtered header
 */
function efpic_bc_custom_header_elements( $custom_header, $id ) {
	$custom_header = '';

	if ( ! empty( get_option( 'efpic_logo' ) ) ) {
		$custom_header .= '<a href="' . esc_url( home_url( '/' ) ) . '"><img id="logo" src="' . get_option( 'efpic_logo' ) . '" alt="" /></a>';
	}

	$custom_header .= '<div class="efpic-header-inner">';

	if (  get_option( 'efpic_site_title' ) == 'on' ) {
			$custom_header .= '<div class="blog-name">' . get_bloginfo( 'name' ) . '</div>';
	}

	$custom_header .= '<div class="efpic-collection-title">'.  get_the_title( $id ) . '</div>';
	$custom_header .= '</div>';

	return $custom_header;
}

add_filter( 'efpic_header', 'efpic_bc_custom_header_elements', 10, 2 );


/**
 * Add logo to the password form.
 * 
 * @since brand-customize (1.4.4)
 *
 * @see efpic/frontend/efpic-app.php
 *
 * @param string $efpic_password_box_content The password form HTML
 * @param int $id The collection ID
 * @return string The filtered password form
 */
function efpic_bc_password_box_content( $efpic_password_box_content, $id ) {
	if ( ! empty( get_option( 'efpic_logo' ) ) ) {
		$efpic_password_box_content = '<a class="efpic-protected__logo" href="' . esc_url( home_url( '/' ) ) . '"><img class="efpic-protected__logo-img" src="' . get_option( 'efpic_logo' ) . '" alt="" /></a>' . $efpic_password_box_content;
	}

	return $efpic_password_box_content;
}

add_filter( 'efpic_password_box_content', 'efpic_bc_password_box_content', 10, 2 );


/**
 * Redirect after sending selection.
 *
 * @since brand-customize (0.0.6)
 *
 * @see efpic/efpic.php
 *
 * @param string $redirect The redirection URL
 * @return string The filtered URL
 */
function efpic_bc_custom_redirect( $redirect ) {
	$after_approval = get_option( 'efpic_after_approval' );

	if ( ! empty( $after_approval['target_url'] ) ) {
		$redirect = esc_url( $after_approval['target_url'] );
	}

	return $redirect;
}

add_filter( 'efpic_redirect', 'efpic_bc_custom_redirect' );


/**
 * Replace default mail message.
 *
 * @since brand-customize (0.0.7)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 *
 * @param string $mail_message The default mail message
 * @param string $user_name The current collection authors's display_name
 * @return string The filtered mail message
 */
function efpic_bc_custom_default_mail_message( $mail_message, $user_name ) {
	$custom_email_texts = get_option( 'efpic_email_templates' );

	if ( ! empty( $custom_email_texts ) ) {
		// Use default, if it is set
		$default = array_search( 1, array_column( $custom_email_texts, 'message_default' ) );

		if ( $default !== false ) {
			$new_mail_message = $custom_email_texts[$default]['message_body'];
		}
	}

	// Possible fallback to old saved message
	// TODO: Can we remove this?
	if ( ! isset( $new_mail_message ) ) {
		$settings_brand_customize = get_option( 'efpic_settings_brand_customize' );

		if ( isset( $settings_brand_customize['custom_email_text'] ) ) {
			$mail_message = $settings_brand_customize['custom_email_text'];
		}
	}
	else {
		$mail_message = $new_mail_message;
	}

	return $mail_message;
}

add_filter( 'efpic_client_mail_message', 'efpic_bc_custom_default_mail_message', 10, 2 );


/**
 * Add message select box to collection edit screen.
 *
 * @since brand-customize (1.4.0)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 * @global object $post The post object
 */
function efpic_bc_load_message_switcher() {
	global $post;

	if ( $post->post_status == 'sent' OR $post->post_status == 'delivered' ) {
		return;
	}

	$custom_email_texts = get_option( 'efpic_email_templates' );

	if ( is_array( $custom_email_texts ) AND ! empty( $custom_email_texts ) ) {
?>
	<p class="efpic-select-custom-message-wrap">
		<svg class="efpic-select-custom-message-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 10L9 4l-6 6"/><path d="M20 20h-7a4 4 0 0 1-4-4V5"/></svg>
		<select class="efpic-select-custom-message js-efpic-select-custom-message" id="efpic-select-custom-message" autocomplete="off">
			<option value="" disabled selected><?php _e( 'Choose message template', 'efpic-pro' ); ?></option>
		<?php
			foreach( $custom_email_texts as $message ) {
				echo '<option value="' . htmlspecialchars( $message['message_body'] ) . '">' . $message['message_name'] . '</option>';
			}
		?>
		</select>
	</p>
<?php
	}
}

add_action( 'efpic_after_collection_description', 'efpic_bc_load_message_switcher' );


/**
 * Filter approved message.
 *
 * @since brand-customize (1.3.0)
 *
 * @see efpic/frontend/js/templates/efpic-approved.php
 * 
 * @param string $message The approved message
 * @return string The filtered message
 */
function efpic_bc_approved_message( $message ) {
	$after_approval = get_option( 'efpic_after_approval' );

	if ( ! empty( $after_approval['after_approval_message'] ) ) {
		$message = $after_approval['after_approval_message'];
	}

	return $message;
}

add_filter( 'efpic_approved_message', 'efpic_bc_approved_message' );


/**
 * Filter time to redirect.
 *
 * @since brand-customize (1.3.0)
 *
 * @see efpic/frontend/js/templates/efpic-approved.php
 *
 * @param string $timer The time to redirect in seconds
 * @return string The filtered time
 */
function efpic_bc_redirect_timer( $timer ) {
	$after_approval = get_option( 'efpic_after_approval' );

	if ( ! empty ( $after_approval['redirect_timer'] ) ) {
		$timer = $after_approval['redirect_timer'];
	}

	return $timer;
}

add_filter( 'efpic_redirect_timer', 'efpic_bc_redirect_timer' );


/**
 * Add custom logo to emails.
 *
 * @since brand-customize (0.0.7)
 * 
 * @see efpic/backend/includes/emails/class-efpic-emails.php
 * 
 * @param string $logo The logo URL
 * @return string The filtered URL
 */
function efpic_bc_email_logo( $logo ) {
	if ( ! empty( get_option( 'efpic_logo' ) ) ) {
		return get_option( 'efpic_logo' );
	}
	else {
		return $logo;
	}
}

add_filter( 'efpic_logo', 'efpic_bc_email_logo' );


/**
 * RGB-to-HSL and HSL-to-RGB Converter
 * Check http://www.michaelburri.ch/generate-different-shades-of-a-color/ for explanation
 * @author     Michael Burri, https://github.com/mpbzh
 * @license    GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// Validates hex color code and returns proper value
// Input: String - Format #ffffff, #fff, ffffff or fff
// Output: hex value - 3 byte (000000 if input is invalid)
function efpic_pro_validate_hex( $hex ) {
	// Complete patterns like #ffffff or #fff
	if( preg_match( "/^#([0-9a-fA-F]{6})$/", $hex ) || preg_match( "/^#([0-9a-fA-F]{3})$/", $hex ) ) {
		// Remove #
		$hex = substr( $hex, 1 );
	}

	// Complete patterns without # like ffffff or 000000
	if( preg_match("/^([0-9a-fA-F]{6})$/", $hex ) ) {
		return $hex;
	}

	// Short patterns without # like fff or 000
	if( preg_match( "/^([0-9a-f]{3})$/", $hex ) ) {
		// Spread to 6 digits
		return substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 2, 1 ) . substr( $hex, 2, 1 );
	}

	// If input value is invalid return black
	return "000000";
}

// Converts hex color code to RGB color
// Input: String - Format #ffffff, #fff, ffffff or fff
// Output: Array(Hue, Saturation, Lightness) - Values from 0 to 1
function efpic_pro_hex2hsl( $hex ) {
	//Validate Hex Input
	$hex = efpic_pro_validate_hex( $hex );

	// Split input by color
	$hex = str_split( $hex, 2 );

	// Convert color values to value between 0 and 1
	$r = ( hexdec( $hex[0] ) ) / 255;
	$g = ( hexdec( $hex[1] ) ) / 255;
	$b = ( hexdec( $hex[2] ) ) / 255;

	return efpic_pro_rgb2hsl( array( $r,$g,$b ) );
}

// Converts RGB color to HSL color
// Check http://en.wikipedia.org/wiki/HSL_and_HSV#Hue_and_chroma for
// details
// Input: Array(Red, Green, Blue) - Values from 0 to 1
// Output: Array(Hue, Saturation, Lightness) - Values from 0 to 1
function efpic_pro_rgb2hsl( $rgb ) {
	// Fill variables $r, $g, $b by array given.
	list( $r, $g, $b ) = $rgb;

	// Determine lowest & highest value and chroma
	$max = max( $r, $g, $b );
	$min = min( $r, $g, $b );
	$chroma = $max - $min;

	// Calculate Luminosity
	$l = ( $max + $min ) / 2;

	// If chroma is 0, the given color is grey
	// therefore hue and saturation are set to 0
	if ( $chroma == 0 ) {
		$h = 0;
		$s = 0;
	}

	// Else calculate hue and saturation.
	// Check http://en.wikipedia.org/wiki/HSL_and_HSV for details
	else {
		switch( $max ) {
			case $r:
				$h_ = fmod( ( ( $g - $b ) / $chroma ), 6);
				if ( $h_ < 0 ) $h_ = ( 6 - fmod( abs( $h_ ), 6 ) ); // Bugfix: fmod() returns wrong values for negative numbers
				break;

			case $g:
				$h_ = ( $b - $r ) / $chroma + 2;
				break;

			case $b:
				$h_ = ( $r - $g ) / $chroma + 4;
				break;

			default:
				break;
		}

		$h = $h_ / 6;
		$s = 1 - abs( 2 * $l - 1 );
	}

	// Return HSL Color as array
	return array( $h, $s, $l );
}

// Converts HSL color to RGB color
// Input: Array(Hue, Saturation, Lightness) - Values from 0 to 1
// Output: Array(Red, Green, Blue) - Values from 0 to 1
function efpic_pro_hsl2rgb( $hsl ) {
	// Fill variables $h, $s, $l by array given.
	list( $h, $s, $l ) = $hsl;

	// If saturation is 0, the given color is grey and only
	// lightness is relevant.
	if ( $s == 0 ) {
		$rgb = array( $l, $l, $l );
	}

	// Else calculate r, g, b according to hue.
	// Check http://en.wikipedia.org/wiki/HSL_and_HSV#From_HSL for details
	else {
		$chroma = ( 1 - abs( 2*$l - 1 ) ) * $s;
		$h_     = $h * 6;
		$x         = $chroma * ( 1 - abs( ( fmod( $h_,2 ) ) - 1 ) ); // Note: fmod because % (modulo) returns int value!!
		$m = $l - round($chroma/2, 10); // Bugfix for strange float behaviour (e.g. $l=0.17 and $s=1)

		if( $h_ >= 0 && $h_ < 1 ) $rgb = array( ( $chroma + $m ), ( $x + $m ), $m );
		else if( $h_ >= 1 && $h_ < 2 ) $rgb = array( ( $x + $m ), ( $chroma + $m ), $m );
		else if( $h_ >= 2 && $h_ < 3 ) $rgb = array( $m, ( $chroma + $m ), ( $x + $m ) );
		else if( $h_ >= 3 && $h_ < 4 ) $rgb = array( $m, ( $x + $m ), ( $chroma + $m ) );
		else if( $h_ >= 4 && $h_ < 5 ) $rgb = array( ( $x + $m ), $m, ( $chroma + $m ) );
		else if( $h_ >= 5 && $h_ < 6 ) $rgb = array( ( $chroma + $m ), $m, ( $x + $m ) );
	}

	return $rgb;
}

// Converts RGB color to hex code
// Input: Array(Red, Green, Blue)
// Output: String hex value (#000000 - #ffffff)
function efpic_pro_rgb2hex( $rgb ) {
	list( $r,$g,$b ) = $rgb;
	$r = round( 255 * $r );
	$g = round( 255 * $g );
	$b = round( 255 * $b );
	return "#".sprintf("%02X",$r).sprintf("%02X",$g).sprintf("%02X",$b);
}

// Converts HSL color to RGB hex code
// Input: Array(Hue, Saturation, Lightness) - Values from 0 to 1
// Output: String hex value (#000000 - #ffffff)
function hsl2hex( $hsl ) {
	$rgb = efpic_pro_hsl2rgb( $hsl );
	return efpic_pro_rgb2hex( $rgb );
}


/**
 * Calculate efpic button text color.
 * 
 * Calculates the contrast of a given color to black, returns black or white for sufficient contrast.
 *
 * @since 1.0.1
 *
 * @see https://stackoverflow.com/questions/1331591/given-a-background-color-black-or-white-text#answer-42921358
 *
 * @param string $bg_color_hex The hex color value
 * @return string The hex color value of the contrast color (either black or white)
 */
function efpic_pro_get_contrast_color( $bg_color_hex ) {
	// hexColor RGB
	$r1 = hexdec( substr( $bg_color_hex, 1, 2 ) );
	$g1 = hexdec( substr( $bg_color_hex, 3, 2 ) );
	$b1 = hexdec( substr( $bg_color_hex, 5, 2 ) );

	// Black RGB
	$black_color = "#000000";
	$r2 = hexdec( substr( $black_color, 1, 2 ) );
	$g2 = hexdec( substr( $black_color, 3, 2 ) );
	$b2 = hexdec( substr( $black_color, 5, 2 ) );

	// Calculate contrast ratio
	$ratio1 = 0.2126 * pow( $r1 / 255, 2.2 ) +
		0.7152 * pow( $g1 / 255, 2.2 ) +
		0.0722 * pow( $b1 / 255, 2.2 );

	$ratio2 = 0.2126 * pow( $r2 / 255, 2.2 ) +
		0.7152 * pow( $g2 / 255, 2.2 ) +
		0.0722 * pow( $b2 / 255, 2.2 );

	$contrast_ratio = 0;
	if ( $ratio1 > $ratio2 ) {
		$contrast_ratio = (int) ( ( $ratio1 + 0.05 ) / ( $ratio2 + 0.05 ) );
	} else {
		$contrast_ratio = (int) ( ( $ratio2 + 0.05 ) / ( $ratio1 + 0.05 ) );
	}

	// If contrast is more than 5, return black
	if ( $contrast_ratio > 5 ) {
		return '#000000';
	} else { 
		// Return white
		return '#ffffff';
	}
}