<?php
/**
 * Security hardening for efpic (CSRF, sanitization, Parsedown safe mode).
 *
 * @since 1.0.21
 */
defined( 'ABSPATH' ) || exit;

/**
 * Require nonce for proof file download.
 */
function efpic_security_proof_download_gate() {
	if ( empty( $_REQUEST['efpic-download'] ) || 'efpic-proof-file' !== $_REQUEST['efpic-download'] ) {
		return;
	}

	if ( ! current_user_can( efpic_capability() ) ) {
		wp_die( esc_html__( 'Forbidden', 'efpic' ), 403 );
	}

	$post_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : 0;
	if ( ! $post_id || 'efpic_collection' !== get_post_type( $post_id ) ) {
		wp_die( esc_html__( 'Invalid collection.', 'efpic' ), 400 );
	}

	$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'efpic_download_proof_' . $post_id ) ) {
		wp_die( esc_html__( 'Security check failed. Please go back and try again.', 'efpic' ), 403 );
	}
}
add_action( 'init', 'efpic_security_proof_download_gate', 5 );

/**
 * Append nonce to proof download URLs in admin.
 *
 * @param string $url URL.
 * @return string
 */
function efpic_security_proof_download_url( $post_id ) {
	$post_id = (int) $post_id;
	return wp_nonce_url(
		admin_url( 'post.php?post=' . $post_id . '&action=edit&efpic-download=efpic-proof-file' ),
		'efpic_download_proof_' . $post_id
	);
}

/**
 * Enable Parsedown safe mode for collection descriptions.
 *
 * @param \efpic\Vendor\Parsedown\Parsedown $parsedown Instance.
 * @return \efpic\Vendor\Parsedown\Parsedown
 */
function efpic_security_enable_parsedown_safe_mode( $parsedown ) {
	if ( is_object( $parsedown ) && method_exists( $parsedown, 'setSafeMode' ) ) {
		$parsedown->setSafeMode( true );
	}
	return $parsedown;
}

/**
 * Harden marker comments on save (additional pass).
 *
 * @param array $save Selection payload.
 * @return array
 */
function efpic_security_sanitize_markers_in_save( $save ) {
	if ( empty( $save['markers'] ) || ! is_array( $save['markers'] ) ) {
		return $save;
	}

	foreach ( $save['markers'] as $image_key => $markers ) {
		if ( ! is_array( $markers ) ) {
			unset( $save['markers'][ $image_key ] );
			continue;
		}
		foreach ( $markers as $i => $marker ) {
			if ( ! is_array( $marker ) ) {
				unset( $save['markers'][ $image_key ][ $i ] );
				continue;
			}
			if ( isset( $marker['comment'] ) ) {
				$save['markers'][ $image_key ][ $i ]['comment'] = sanitize_textarea_field( $marker['comment'] );
			}
			if ( isset( $marker['x'] ) ) {
				$save['markers'][ $image_key ][ $i ]['x'] = floatval( $marker['x'] );
			}
			if ( isset( $marker['y'] ) ) {
				$save['markers'][ $image_key ][ $i ]['y'] = floatval( $marker['y'] );
			}
		}
	}

	if ( ! empty( $save['stars'] ) && is_array( $save['stars'] ) ) {
		foreach ( $save['stars'] as $key => $stars ) {
			$save['stars'][ $key ] = max( 0, min( 5, intval( $stars ) ) );
		}
	}

	return $save;
}
add_filter( 'efpic_sanitize_save', 'efpic_security_sanitize_markers_in_save', 20 );

/**
 * Prefer stronger random collection post slugs (post_name), not the rewrite base.
 *
 * @param string $slug Generated post_name.
 * @return string
 */
function efpic_security_stronger_post_slug( $slug ) {
	if ( 'on' !== get_option( 'efpic_random_slugs', 'on' ) ) {
		return $slug;
	}
	if ( strlen( $slug ) >= 12 ) {
		return $slug;
	}
	try {
		return bin2hex( random_bytes( 8 ) );
	} catch ( Exception $e ) {
		return $slug;
	}
}
add_filter( 'efpic_generate_collection_slug', 'efpic_security_stronger_post_slug', 10 );
