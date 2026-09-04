<?php
/**
 * IPTC metadata extraction for efpic images.
 *
 * @since 1.0.21
 */
defined( 'ABSPATH' ) || exit;

/**
 * Read IPTC title, description and keywords from an image file.
 *
 * @param string $file Absolute path.
 * @return array{title:string,description:string,keywords:string}
 */
function efpic_read_iptc_from_file( $file ) {
	$result = array(
		'title'       => '',
		'description' => '',
		'keywords'    => '',
	);

	if ( empty( $file ) || ! is_readable( $file ) || ! function_exists( 'iptcparse' ) ) {
		return $result;
	}

	$info = array();
	@getimagesize( $file, $info );
	if ( empty( $info['APP13'] ) ) {
		return $result;
	}

	$iptc = @iptcparse( $info['APP13'] );
	if ( empty( $iptc ) || ! is_array( $iptc ) ) {
		return $result;
	}

	if ( ! empty( $iptc['2#005'][0] ) ) {
		$result['title'] = sanitize_text_field( $iptc['2#005'][0] );
	} elseif ( ! empty( $iptc['2#105'][0] ) ) {
		$result['title'] = sanitize_text_field( $iptc['2#105'][0] );
	}

	if ( ! empty( $iptc['2#120'][0] ) ) {
		$result['description'] = sanitize_textarea_field( $iptc['2#120'][0] );
	}

	if ( ! empty( $iptc['2#025'] ) && is_array( $iptc['2#025'] ) ) {
		$keywords = array_map( 'sanitize_text_field', $iptc['2#025'] );
		$result['keywords'] = implode( ', ', array_filter( $keywords ) );
	}

	return $result;
}

/**
 * Persist IPTC fields onto attachment meta after WP generates metadata.
 *
 * @param array $metadata      Attachment metadata.
 * @param int   $attachment_id Attachment ID.
 * @return array
 */
function efpic_store_iptc_on_metadata( $metadata, $attachment_id ) {
	$file = get_attached_file( $attachment_id );
	if ( empty( $file ) ) {
		return $metadata;
	}

	$parent = wp_get_post_parent_id( $attachment_id );
	if ( $parent && 'efpic_collection' !== get_post_type( $parent ) ) {
		// Still store IPTC for any image; cheap and useful if later attached.
	}

	$iptc = efpic_read_iptc_from_file( $file );

	update_post_meta( $attachment_id, '_efpic_iptc_title', $iptc['title'] );
	update_post_meta( $attachment_id, '_efpic_iptc_description', $iptc['description'] );
	update_post_meta( $attachment_id, '_efpic_iptc_keywords', $iptc['keywords'] );

	if ( ! isset( $metadata['image_meta'] ) || ! is_array( $metadata['image_meta'] ) ) {
		$metadata['image_meta'] = array();
	}

	if ( $iptc['title'] && empty( $metadata['image_meta']['title'] ) ) {
		$metadata['image_meta']['title'] = $iptc['title'];
	}
	if ( $iptc['description'] && empty( $metadata['image_meta']['caption'] ) ) {
		$metadata['image_meta']['caption'] = $iptc['description'];
	}
	if ( $iptc['keywords'] ) {
		$metadata['image_meta']['keywords'] = $iptc['keywords'];
	}

	return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'efpic_store_iptc_on_metadata', 20, 2 );

/**
 * Get IPTC fields for an attachment (meta first, then file fallback).
 *
 * @param int $attachment_id Attachment ID.
 * @return array{title:string,description:string,keywords:string}
 */
function efpic_get_attachment_iptc( $attachment_id ) {
	$title       = (string) get_post_meta( $attachment_id, '_efpic_iptc_title', true );
	$description = (string) get_post_meta( $attachment_id, '_efpic_iptc_description', true );
	$keywords    = (string) get_post_meta( $attachment_id, '_efpic_iptc_keywords', true );

	if ( '' === $title && '' === $description && '' === $keywords ) {
		$meta = wp_get_attachment_metadata( $attachment_id );
		if ( ! empty( $meta['image_meta']['title'] ) ) {
			$title = (string) $meta['image_meta']['title'];
		}
		if ( ! empty( $meta['image_meta']['caption'] ) ) {
			$description = (string) $meta['image_meta']['caption'];
		}
		if ( ! empty( $meta['image_meta']['keywords'] ) ) {
			$keywords = is_array( $meta['image_meta']['keywords'] )
				? implode( ', ', $meta['image_meta']['keywords'] )
				: (string) $meta['image_meta']['keywords'];
		}
	}

	return array(
		'title'       => $title,
		'description' => $description,
		'keywords'    => $keywords,
	);
}

/**
 * Build searchable text blob for client text filter.
 *
 * @param int   $attachment_id Attachment ID.
 * @param array $image_data    Current image payload.
 * @return string
 */
function efpic_build_image_search_text( $attachment_id, $image_data = array() ) {
	$parts = array();

	$filename = efpic_get_image_filename( $attachment_id );
	if ( $filename ) {
		$parts[] = $filename;
	}

	$iptc = efpic_get_attachment_iptc( $attachment_id );
	foreach ( array( 'title', 'description', 'keywords' ) as $key ) {
		if ( ! empty( $iptc[ $key ] ) ) {
			$parts[] = $iptc[ $key ];
		}
	}

	if ( ! empty( $image_data['description'] ) ) {
		$parts[] = wp_strip_all_tags( $image_data['description'] );
	}

	if ( ! empty( $image_data['title'] ) && is_array( $image_data['title'] ) ) {
		$parts[] = implode( ' ', array_map( 'strval', $image_data['title'] ) );
	}

	return strtolower( trim( preg_replace( '/\s+/', ' ', implode( ' ', $parts ) ) ) );
}
