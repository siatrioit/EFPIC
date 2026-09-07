<?php
/**
 * Photographer social links in client galleries.
 *
 * Global URLs + default On/Off in Settings → Design/Appearance.
 * Per-collection On/Off override in the collection editor.
 *
 * @since 1.0.29
 */
defined( 'ABSPATH' ) || exit;

/**
 * Supported social networks (option key suffix => label).
 *
 * @return array<string,string>
 */
function efpic_social_link_networks() {
	return array(
		'instagram' => __( 'Instagram', 'efpic' ),
		'facebook'  => __( 'Facebook', 'efpic' ),
		'tiktok'    => __( 'TikTok', 'efpic' ),
		'youtube'   => __( 'YouTube', 'efpic' ),
		'website'   => __( 'Website', 'efpic' ),
	);
}

/**
 * Get configured social URLs (non-empty only).
 *
 * @return array<string,string> network => url
 */
function efpic_get_social_link_urls() {
	$urls = array();
	foreach ( array_keys( efpic_social_link_networks() ) as $network ) {
		$url = trim( (string) get_option( 'efpic_social_' . $network, '' ) );
		if ( '' === $url ) {
			continue;
		}
		if ( ! preg_match( '#^https?://#i', $url ) ) {
			$url = 'https://' . $url;
		}
		$urls[ $network ] = esc_url( $url );
	}
	return $urls;
}

/**
 * Whether social links should show for a collection.
 *
 * @param int $post_id Collection ID.
 * @return bool
 */
function efpic_social_links_enabled_for_collection( $post_id ) {
	$urls = efpic_get_social_link_urls();
	if ( empty( $urls ) ) {
		return false;
	}

	$meta = get_post_meta( (int) $post_id, '_efpic_social_links', true );
	if ( 'off' === $meta ) {
		return false;
	}
	if ( 'on' === $meta ) {
		return true;
	}

	return 'on' === get_option( 'efpic_social_links_enabled', 'on' );
}

/**
 * Add social link settings under Design/Appearance.
 *
 * @param array $settings Settings groups.
 * @return array
 */
function efpic_social_links_add_settings( $settings ) {
	if ( empty( $settings['design-appearance']['settings'] ) ) {
		return $settings;
	}

	$settings['design-appearance']['settings']['social_links_panel'] = array(
		'type'   => 'html',
		'output' => 'efpic_social_links_settings_html',
	);

	foreach ( efpic_social_link_networks() as $key => $label ) {
		$settings['design-appearance']['settings'][ 'social_' . $key ] = array(
			'type'        => 'text',
			'label'       => $label,
			'description' => sprintf(
				/* translators: %s = network name */
				__( 'Full URL to your %s profile (leave empty to hide).', 'efpic' ),
				$label
			),
			'placeholder' => 'https://',
			'default'     => '',
			'validation'  => 'efpic_sanitize_social_url',
		);
	}

	return $settings;
}
add_filter( 'efpic_settings', 'efpic_social_links_add_settings', 25 );

/**
 * Sanitize a social profile URL.
 *
 * @param string $value Raw value.
 * @return string
 */
function efpic_sanitize_social_url( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	if ( ! preg_match( '#^https?://#i', $value ) ) {
		$value = 'https://' . $value;
	}
	return esc_url_raw( $value );
}

/**
 * Register master On/Off option (HTML panel is skipped by auto-register).
 */
function efpic_social_links_register_setting() {
	register_setting(
		'efpic_design-appearance',
		'efpic_social_links_enabled',
		array(
			'type'              => 'string',
			'sanitize_callback' => function( $value ) {
				return ( 'off' === $value ) ? 'off' : 'on';
			},
			'default'           => 'on',
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', 'efpic_social_links_register_setting' );

/**
 * Settings panel: default On/Off for galleries.
 *
 * @return string
 */
function efpic_social_links_settings_html() {
	$value = get_option( 'efpic_social_links_enabled', 'on' );
	ob_start();
	?>
	<fieldset class="efpic_settings__settings-item" id="efpic_setting--social_links_enabled">
		<h2><?php esc_html_e( 'Social links in galleries', 'efpic' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Show your social profile links in client galleries. You can turn them off for individual collections.', 'efpic' ); ?></p>
		<?php echo efpic_feature_on_off_toggle( 'efpic_social_links_enabled', $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</fieldset>
	<?php
	return ob_get_clean();
}

/**
 * Effective On/Off for the collection editor UI.
 *
 * @param int $post_id Collection ID.
 * @return string on|off
 */
function efpic_social_links_collection_effective_state( $post_id ) {
	$meta = get_post_meta( (int) $post_id, '_efpic_social_links', true );
	if ( 'on' === $meta || 'off' === $meta ) {
		return $meta;
	}
	return ( 'on' === get_option( 'efpic_social_links_enabled', 'on' ) ) ? 'on' : 'off';
}

/**
 * Per-collection On/Off in the status sidebar.
 *
 * @param WP_Post $post Collection post.
 */
function efpic_social_links_collection_post_option( $post ) {
	if ( empty( $post->ID ) || 'efpic_collection' !== $post->post_type ) {
		return;
	}

	// Only show toggle when at least one URL is configured.
	if ( empty( efpic_get_social_link_urls() ) ) {
		return;
	}

	$value = efpic_social_links_collection_effective_state( $post->ID );
	?>
	<div class="efpic-option-item efpic-social-links-option">
		<span class="efpic-social-links-option__label"><?php esc_html_e( 'Social links', 'efpic' ); ?></span>
		<?php echo efpic_feature_on_off_toggle( 'efpic_collection_social_links', $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span class="efpic-hint"><?php esc_html_e( 'Show photographer social links in this gallery.', 'efpic' ); ?></span>
	</div>
	<?php
}
add_action( 'efpic_collection_post_options', 'efpic_social_links_collection_post_option' );

/**
 * Save per-collection social links toggle.
 *
 * @param int $post_id Collection ID.
 */
function efpic_social_links_save_collection_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( efpic_capability() ) ) {
		return;
	}
	if ( ! isset( $_POST['efpic_collection_social_links'] ) ) {
		return;
	}

	$value = sanitize_key( wp_unslash( $_POST['efpic_collection_social_links'] ) );
	update_post_meta( $post_id, '_efpic_social_links', ( 'off' === $value ) ? 'off' : 'on' );
}
add_action( 'save_post_efpic_collection', 'efpic_social_links_save_collection_meta', 12 );

/**
 * SVG icon for a network.
 *
 * @param string $network Network key.
 * @return string
 */
function efpic_social_link_icon_svg( $network ) {
	$icons = array(
		'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm5 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm6.5-.9a1.1 1.1 0 1 0 0 2.2 1.1 1.1 0 0 0 0-2.2zM12 9a3 3 0 1 1 0 6 3 3 0 0 1 0-6z"/></svg>',
		'facebook'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14 8h3V4h-3c-2.8 0-5 2.2-5 5v2H7v4h2v7h4v-7h3.1l.9-4H13V9c0-.6.4-1 1-1z"/></svg>',
		'tiktok'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14 3h2.2c.3 1.8 1.5 3.3 3.2 4V9c-1.4-.1-2.7-.6-3.8-1.4v6.6A5.8 5.8 0 1 1 9.2 8.5v2.3a3.5 3.5 0 1 0 2.6 3.4V3H14z"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M23 12.2s0-3.1-.4-4.5c-.2-1.1-1.1-2-2.2-2.2C18.1 5 12 5 12 5s-6.1 0-8.4.5c-1.1.2-2 1.1-2.2 2.2C1 9.1 1 12.2 1 12.2s0 3.1.4 4.5c.2 1.1 1.1 2 2.2 2.2 2.3.5 8.4.5 8.4.5s6.1 0 8.4-.5c1.1-.2 2-1.1 2.2-2.2.4-1.4.4-4.5.4-4.5zM9.8 15.5v-6.6l6.3 3.3-6.3 3.3z"/></svg>',
		'website'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm7.9 9h-3.2a15 15 0 0 0-1.3-5 8 8 0 0 1 4.5 5zM12 4c.9 0 2.3 2.1 3 7H9c.7-4.9 2.1-7 3-7zM4.1 11h3.2a15 15 0 0 1 1.3-5 8 8 0 0 0-4.5 5zM7.3 13H4.1a8 8 0 0 0 4.5 5 15 15 0 0 1-1.3-5zm1.7 0h6c-.7 4.9-2.1 7-3 7s-2.3-2.1-3-7zm6.7 0h3.2a15 15 0 0 1-1.3 5 8 8 0 0 0 4.5-5h-3.2z"/></svg>',
	);

	return isset( $icons[ $network ] ) ? $icons[ $network ] : '';
}

/**
 * Inject social links into gallery header (after Pro/brand filters).
 *
 * Brand Customize replaces the whole header HTML — this keeps icons visible.
 *
 * @param string $html    Header HTML.
 * @param int    $post_id Collection ID.
 * @return string
 */
function efpic_social_links_inject_header( $html, $post_id ) {
	if ( false !== strpos( $html, 'efpic-social-links' ) ) {
		return $html;
	}

	ob_start();
	efpic_render_social_links( $post_id );
	$social = ob_get_clean();
	if ( '' === $social ) {
		return $html;
	}

	if ( false !== strpos( $html, 'efpic-header-top' ) ) {
		return preg_replace(
			'/(<div class="efpic-header-top">)/',
			'$1' . $social,
			$html,
			1
		);
	}

	if ( preg_match( '/<div class="blog-name">.*?<\/div>/s', $html ) ) {
		return preg_replace(
			'/(<div class="blog-name">.*?<\/div>)/s',
			'<div class="efpic-header-top">$1' . $social . '</div>',
			$html,
			1
		);
	}

	if ( false !== strpos( $html, 'efpic-header-inner' ) ) {
		return preg_replace(
			'/(<div class="efpic-header-inner">)/',
			'$1<div class="efpic-header-top">' . $social . '</div>',
			$html,
			1
		);
	}

	return $html . $social;
}
add_filter( 'efpic_header', 'efpic_social_links_inject_header', 30, 2 );

/**
 * Render social links in the client gallery.
 *
 * @param int|null $post_id Collection ID.
 */
function efpic_render_social_links( $post_id = null ) {
	if ( null === $post_id ) {
		$post_id = get_the_ID();
	}
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || ! efpic_social_links_enabled_for_collection( $post_id ) ) {
		return;
	}

	$urls     = efpic_get_social_link_urls();
	$networks = efpic_social_link_networks();
	if ( empty( $urls ) ) {
		return;
	}
	?>
	<nav class="efpic-social-links" aria-label="<?php echo esc_attr__( 'Social links', 'efpic' ); ?>">
		<?php foreach ( $urls as $network => $url ) : ?>
			<a class="efpic-social-links__item efpic-social-links__item--<?php echo esc_attr( $network ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $networks[ $network ] ); ?>">
				<span class="efpic-social-links__icon"><?php echo efpic_social_link_icon_svg( $network ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="screen-reader-text"><?php echo esc_html( $networks[ $network ] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php
}
