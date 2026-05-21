<?php
/**
 * Watermarking functionality
 *
 * @since theft-protection (1.6.0)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Add watermark.
 *
 * Applies a watermark and replaces the original image.
 *
 * @since theft-protection (0.0.2)
 *
 * @param array $upload Containing file path and file type 
 * @param int $post_id Optional. The collection post ID
 * @param bool $skip_type_check Whether to skip the file type check
 * @return array Original array with file path and type
 */
function efpic_apply_watermark( $upload, $post_id = '', $skip_type_check = false ) {

	// Only apply the watermark to efpic images
	if ( $post_id == '' AND ( ! isset( $_POST['post_id'] ) || $_POST['post_id'] < 0 ) ) {
		return $upload;
	}

	if ( $skip_type_check == false AND get_post_type( $_POST['post_id'] ) != 'efpic_collection' ) {
		return $upload;
	}

	// Get watermark options
	$watermark_options = get_option( 'efpic_watermark' );

	// Exit, if watermark isn't set
	if ( empty( $watermark_options['watermark'] ) ) {
		return $upload;
	}

	// Do not add watermark to delivery images
	if ( get_post_status( $_POST['post_id'] ) == 'delivery-draft' ) {
		return $upload;
	}

	// Check if watermarking is active for this collection
	$apply_watermark = get_post_meta( $_POST['post_id'], '_efpic_apply_watermark', true );

	if ( $apply_watermark AND $apply_watermark != 'on' ) {
		return $upload;
	}

	// Check if watermarking is enabled by default
	elseif ( isset( $watermark_options['watermark_by_default'] ) AND $watermark_options['watermark_by_default'] == 'off' AND ( ! $apply_watermark OR $apply_watermark == 'off' ) ) {
		return $upload;
	}

	// Prepare stuff
	$image = $upload['file'];
	$type =  $upload['type'];
	$size = getimagesize( $image );

	// Get path to watermark image
	$watermark_path = get_attached_file( $watermark_options['watermark'] );

	// Get image type, use corresponding function to create image
	$watermark_mime_type = get_post_mime_type( $watermark_options['watermark'] );

	switch ( $watermark_mime_type ) {
		case 'image/jpeg':
			$watermark = imagecreatefromjpeg( $watermark_path );
			break;
		case 'image/png':
			$watermark = imagecreatefrompng( $watermark_path );
			break;
		case 'image/gif':
			$watermark = imagecreatefromgif( $watermark_path );
		break;
		default:
			return $upload;
	}

	// Get watermark dimensions
	$watermark_width = imagesx( $watermark );
	$watermark_height = imagesy( $watermark );

	// Calculate right size for watermark
	if ( $watermark_options['watermark_sizing'] == 'fill' ) {
		// If sizing is set to fill width and height are 100%
		$new_watermark_width = $watermark_width;
		$new_watermark_height = $watermark_height;
	}
	else {
		$watermark_size = $watermark_options['watermark_size'];
		$watermark_size = 100 / $watermark_size;

		// Check watermark orientation, adjust size
		if ( $watermark_width / $watermark_height > 1 ) { // landscape
			$new_watermark_width = $size[0] / $watermark_size;
			$new_watermark_height = $new_watermark_width / $watermark_width * $watermark_height;	
		}
		elseif ( $watermark_width / $watermark_height < 1 ) { // portrait
			$new_watermark_height = $size[1] / $watermark_size;
			$new_watermark_width = $new_watermark_height / $watermark_height * $watermark_width;	
		}
		else { // square
			// Check original image oriantation, adjust size
			// Makes sure watermark is not > 100% of width/height
			if ( $size[0] / $size[1] > 1 ) { // landscape
				$new_watermark_height = $size[1] / $watermark_size;
				$new_watermark_width = $new_watermark_height / $watermark_height * $watermark_width;	
			}
			elseif ( $size[0] / $size[1] < 1 ) {
				$new_watermark_width = $size[0] / $watermark_size;
				$new_watermark_height = $new_watermark_width / $watermark_width * $watermark_height;	
			}
			else {
				$new_watermark_width = $size[0] / $watermark_size;
				$new_watermark_height = $new_watermark_width / $watermark_width * $watermark_height;
			}
		}
	}

	// Calculate position
	if ( $watermark_options['watermark_sizing'] == 'fill' ) {
		// If sizing is set to fill position is centered
		$dest_x = ( $size[0] - $new_watermark_width ) / 2;
		$dest_y = ( $size[1] - $new_watermark_height ) / 2;
	}
	else {
		// Get watermark position setting
		$watermark_position = $watermark_options['watermark_position'];
		$margin = 5;

		// Calculate watermark position
		switch ( $watermark_position ) {
			case 'top-left':
				$dest_x = 0 + $margin;
				$dest_y = 0 + $margin;
				break;
			case 'top-center':
				$dest_x = ( $size[0] - $new_watermark_width ) / 2;
				$dest_y = 0 + $margin;
				break;
			case 'top-right':
				$dest_x = $size[0] - $new_watermark_width - $margin;
				$dest_y = 0 + $margin;
				break;
			case 'middle-left':
				$dest_x = 0;
				$dest_y = ( $size[1] - $new_watermark_height ) / 2;
				break;
			case 'middle-center':
				$dest_x = ( $size[0] - $new_watermark_width ) / 2;
				$dest_y = ( $size[1] - $new_watermark_height ) / 2;
				break;
			case 'middle-right':
				$dest_x = $size[0] - $new_watermark_width - $margin;
				$dest_y = ( $size[1] - $new_watermark_height ) / 2;
				break;
			case 'bottom-left':
				$dest_x = 0 + $margin;
				$dest_y = $size[1] - $new_watermark_height - $margin;
				break;
			case 'bottom-center':
				$dest_x = ( $size[0] - $new_watermark_width ) / 2;
				$dest_y = $size[1] - $new_watermark_height - $margin;
				break;
			default: // case 'bottom-right':
				$dest_x = $size[0] - $new_watermark_width - $margin;
				$dest_y = $size[1] - $new_watermark_height - $margin;
				break;
		}
	}

	// Create new image
	switch ( $type ) {
		case 'image/jpeg':
			$new_image = imagecreatefromjpeg( $image );
			break;
		case 'image/png':
			$new_image = imagecreatefrompng( $image );
			break;
		case 'image/gif':
			$new_image = imagecreatefromgif( $image );
			break;
		default:
			return $upload;
	}

	// Read and apply rotation from meta data
	// Orientation value info here: http://exif.org/Exif2-2.PDF (page 18)
	$exif = exif_read_data( $image );

	if ( ! empty( $exif['Orientation'] ) && $exif['Orientation'] ) {
		$orientation = $exif['Orientation'];
		switch ( $orientation ) {
			case 2:
				imageflip( $new_image, IMG_FLIP_HORIZONTAL );
				break;
			case 3:
				$new_image = imagerotate( $new_image, 180, 0 );
				break;
			case 4:
				imageflip( $new_image, IMG_FLIP_VERTICAL );
				break;
			case 5:
				$new_image = imagerotate( $new_image, -90, 0 );
				imageflip( $new_image, IMG_FLIP_HORIZONTAL );
				break;
			case 6:
				$new_image = imagerotate( $new_image, -90, 0 );
				break;
			case 7:
				$new_image = imagerotate( $new_image, 90, 0 );
				imageflip( $new_image, IMG_FLIP_HORIZONTAL );
				break;
			case 8:
				$new_image = imagerotate( $new_image, 90, 0 );
				break;
		}
	}

	// Combine images
	$temp = imagecopyresampled( $new_image, $watermark, $dest_x, $dest_y, 0, 0, $new_watermark_width, $new_watermark_height, $watermark_width, $watermark_height );

	imagejpeg( $new_image, $image );

	// Delete temporary images
	imagedestroy( $new_image );
	imagedestroy( $watermark );

	return $upload;
}

add_filter( 'wp_handle_upload', 'efpic_apply_watermark' );