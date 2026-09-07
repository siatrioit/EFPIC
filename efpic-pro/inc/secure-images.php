<?php
/**
 * Prevent direct access to efpic collection image files.
 *
 * @since 1.0.21
 */
defined( 'EFPIC_PRO' ) || exit;

/**
 * Whether direct image access prevention is enabled.
 *
 * @return bool
 */
function efpic_prevent_direct_access_enabled() {
	return 'on' === get_option( 'efpic_prevent_direct_access', 'off' );
}

/**
 * Add security setting: prevent direct image access (On/Off buttons).
 *
 * @param array $settings Settings groups.
 * @return array
 */
function efpic_secure_images_add_setting( $settings ) {
	if ( empty( $settings['security'] ) ) {
		$settings['security'] = array(
			'title'       => __( 'Security', 'efpic-pro' ),
			'description' => '',
			'settings'    => array(),
			'priority'    => 40,
		);
	}

	$settings['security']['settings']['prevent_direct_access'] = array(
		'type'        => 'html',
		'output'      => 'efpic_secure_images_setting_html',
		'title'       => __( 'Prevent direct image access', 'efpic-pro' ),
		'description' => '',
		'default'     => 'off',
	);

	return $settings;
}
add_filter( 'efpic_settings', 'efpic_secure_images_add_setting', 30 );

/**
 * On/Off toggle UI for prevent direct access.
 *
 * @return string
 */
function efpic_secure_images_setting_html() {
	$value = get_option( 'efpic_prevent_direct_access', 'off' );
	ob_start();
	?>
	<fieldset class="efpic_settings__settings-item">
		<h2><?php esc_html_e( 'Prevent direct image access', 'efpic-pro' ); ?></h2>
		<p class="description"><?php esc_html_e( 'When enabled, collection images can only be loaded from within an efpic gallery (not via a direct file URL).', 'efpic-pro' ); ?></p>
		<?php
		if ( function_exists( 'efpic_feature_on_off_toggle' ) ) {
			echo efpic_feature_on_off_toggle( 'efpic_prevent_direct_access', $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</fieldset>
	<?php
	return ob_get_clean();
}

/**
 * Persist toggle when settings are saved.
 */
function efpic_secure_images_register_setting() {
	register_setting(
		'efpic_security',
		'efpic_prevent_direct_access',
		array(
			'type'              => 'string',
			'sanitize_callback' => function( $value ) {
				$on = ( 'on' === $value );
				efpic_secure_images_sync_htaccess( $on );
				return $on ? 'on' : 'off';
			},
			'default'           => 'off',
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', 'efpic_secure_images_register_setting' );

/**
 * Write / remove deny rules in uploads/efpic/.htaccess.
 *
 * @param bool $enable Whether to deny direct access.
 */
function efpic_secure_images_sync_htaccess( $enable ) {
	$dir = defined( 'EFPIC_UPLOAD_DIR' ) ? EFPIC_UPLOAD_DIR : ( wp_upload_dir()['basedir'] . '/efpic' );
	if ( ! wp_mkdir_p( $dir ) ) {
		return;
	}

	$path = trailingslashit( $dir ) . '.htaccess';
	$marker_start = '# BEGIN efpic-secure-images';
	$marker_end   = '# END efpic-secure-images';
	$rules = $marker_start . "\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" . $marker_end . "\n";

	$existing = file_exists( $path ) ? (string) file_get_contents( $path ) : '';
	$existing = preg_replace( '/' . preg_quote( $marker_start, '/' ) . '.*?' . preg_quote( $marker_end, '/' ) . '\s*/s', '', $existing );

	if ( $enable ) {
		$existing = trim( $existing ) . ( $existing ? "\n" : '' ) . $rules;
	}

	if ( '' === trim( $existing ) ) {
		if ( file_exists( $path ) ) {
			@unlink( $path );
		}
		return;
	}

	file_put_contents( $path, $existing );
}

/**
 * Set access cookie when viewing a collection.
 */
function efpic_secure_images_set_collection_cookie() {
	if ( ! efpic_prevent_direct_access_enabled() ) {
		return;
	}
	if ( ! is_singular( 'efpic_collection' ) ) {
		return;
	}

	$collection_id = get_the_ID();
	$token = hash_hmac( 'sha256', 'efpic-col-' . $collection_id, wp_salt( 'auth' ) );
	$cookie_name = 'efpic_img_access_' . (int) $collection_id;

	if ( empty( $_COOKIE[ $cookie_name ] ) || ! hash_equals( $token, (string) $_COOKIE[ $cookie_name ] ) ) {
		setcookie( $cookie_name, $token, time() + DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
		$_COOKIE[ $cookie_name ] = $token;
	}
}
add_action( 'template_redirect', 'efpic_secure_images_set_collection_cookie', 5 );

/**
 * Whether current request may view a collection's images.
 *
 * @param int $collection_id Collection post ID.
 * @return bool
 */
function efpic_secure_images_can_access_collection( $collection_id ) {
	$collection_id = (int) $collection_id;
	if ( $collection_id < 1 ) {
		return false;
	}

	if ( current_user_can( efpic_capability() ) ) {
		return true;
	}

	$cookie_name = 'efpic_img_access_' . $collection_id;
	$expected    = hash_hmac( 'sha256', 'efpic-col-' . $collection_id, wp_salt( 'auth' ) );
	if ( ! empty( $_COOKIE[ $cookie_name ] ) && hash_equals( $expected, (string) $_COOKIE[ $cookie_name ] ) ) {
		return true;
	}

	// Client Access session (email magic link).
	if ( function_exists( 'efpic_client_access_email' ) ) {
		$email = efpic_client_access_email();
		if ( $email && function_exists( 'efpic_get_ident_from_email' ) ) {
			$ident = efpic_get_ident_from_email( $collection_id, $email );
			if ( ! empty( $ident ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Rewrite collection image URLs to the secure proxy.
 *
 * @param array $image_item Image payload.
 * @return array
 */
function efpic_secure_images_rewrite_collection_item( $image_item, $current_image = null, $post = null ) {
	if ( ! efpic_prevent_direct_access_enabled() || empty( $image_item['imageID'] ) ) {
		return $image_item;
	}

	$id = (int) $image_item['imageID'];
	$map = array(
		'imagePath'            => 'efpic-large',
		'imagePath_small'      => 'efpic-small',
		'imagePath_original'   => 'full',
	);

	foreach ( $map as $key => $size ) {
		if ( ! empty( $image_item[ $key ] ) ) {
			$image_item[ $key ] = efpic_secure_images_url( $id, $size );
		}
	}

	// Drop public srcsets; proxy URLs per size instead.
	$image_item['imagePath_srcset']       = '';
	$image_item['imagePath_small_srcset'] = '';

	return $image_item;
}
add_filter( 'efpic_image_collection_item', 'efpic_secure_images_rewrite_collection_item', 50, 3 );

/**
 * Build secure image URL.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $size          Size slug.
 * @return string
 */
function efpic_secure_images_url( $attachment_id, $size = 'efpic-large' ) {
	$attachment_id = (int) $attachment_id;
	$size          = sanitize_key( $size );
	$tok           = hash_hmac( 'sha256', $attachment_id . '|' . $size, wp_salt( 'nonce' ) );

	return add_query_arg(
		array(
			'efpic_secure_image' => 1,
			'id'                 => $attachment_id,
			'size'               => $size,
			'tok'                => substr( $tok, 0, 32 ),
		),
		home_url( '/' )
	);
}

/**
 * Serve secure image if authorized.
 */
function efpic_secure_images_serve() {
	if ( empty( $_GET['efpic_secure_image'] ) ) {
		return;
	}

	$attachment_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$size          = isset( $_GET['size'] ) ? sanitize_key( $_GET['size'] ) : 'efpic-large';
	$tok           = isset( $_GET['tok'] ) ? (string) $_GET['tok'] : '';

	$expected = substr( hash_hmac( 'sha256', $attachment_id . '|' . $size, wp_salt( 'nonce' ) ), 0, 32 );
	if ( ! $attachment_id || ! hash_equals( $expected, $tok ) ) {
		status_header( 403 );
		exit;
	}

	$parent = (int) wp_get_post_parent_id( $attachment_id );
	if ( $parent && 'efpic_collection' === get_post_type( $parent ) ) {
		if ( ! efpic_secure_images_can_access_collection( $parent ) ) {
			status_header( 403 );
			exit;
		}
	} elseif ( ! current_user_can( efpic_capability() ) ) {
		status_header( 403 );
		exit;
	}

	$path = '';
	if ( 'full' === $size ) {
		$path = get_attached_file( $attachment_id );
	} else {
		$meta = wp_get_attachment_metadata( $attachment_id );
		$base = get_attached_file( $attachment_id );
		$dir  = $base ? trailingslashit( dirname( $base ) ) : '';
		if ( $dir && ! empty( $meta['sizes'][ $size ]['file'] ) ) {
			$path = $dir . $meta['sizes'][ $size ]['file'];
		}
		if ( ! $path || ! file_exists( $path ) ) {
			$path = $base;
		}
	}

	if ( ! $path || ! is_readable( $path ) ) {
		status_header( 404 );
		exit;
	}

	$mime = wp_check_filetype( $path );
	$type = ! empty( $mime['type'] ) ? $mime['type'] : 'application/octet-stream';

	nocache_headers();
	header( 'Content-Type: ' . $type );
	header( 'Content-Length: ' . filesize( $path ) );
	header( 'X-Content-Type-Options: nosniff' );
	readfile( $path );
	exit;
}
add_action( 'template_redirect', 'efpic_secure_images_serve', 1 );

/**
 * Ensure .htaccess matches option on init (self-heal).
 */
function efpic_secure_images_boot() {
	if ( efpic_prevent_direct_access_enabled() ) {
		efpic_secure_images_sync_htaccess( true );
	}
}
add_action( 'init', 'efpic_secure_images_boot', 30 );
