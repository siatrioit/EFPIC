<?php
/**
 * Tukšas reklāmu / pielāgotā satura vietas (bez trešo pušu reklāmām).
 *
 * @since 3.5.2-custom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render an empty admin slot for future custom content.
 *
 * @param string $slot_id      Unique slot identifier (used in CSS class).
 * @param string $wrapper_class Optional extra wrapper classes (legacy layout hooks).
 */
function efpic_render_ad_slot( $slot_id, $wrapper_class = '' ) {
	$classes = array(
		'efpic-ad-slot',
		'efpic-ad-slot--' . sanitize_html_class( $slot_id ),
	);

	if ( ! empty( $wrapper_class ) ) {
		$classes[] = $wrapper_class;
	}

	printf(
		'<div class="%1$s" data-efpic-ad-slot="%2$s" aria-hidden="true"></div>',
		esc_attr( implode( ' ', $classes ) ),
		esc_attr( $slot_id )
	);
}

/**
 * Minimal styling so reserved areas keep layout in the admin.
 */
function efpic_ad_slots_admin_styles() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( empty( $screen ) || strpos( $screen->id, 'efpic' ) === false ) {
		return;
	}
	?>
	<style>
		.efpic-ad-slot {
			display: block;
			min-height: 48px;
			margin: 12px 0;
			border: 1px dashed #c3c4c7;
			border-radius: 4px;
			background: transparent;
			box-sizing: border-box;
		}
		.efpic-ad-slot--pro-metabox {
			min-height: 140px;
		}
		.efpic-ad-slot--pro-hint {
			min-height: 72px;
		}
		.efpic-ad-slot--pro-box {
			min-height: 160px;
		}
		.efpic-ad-slot--bf-banner {
			min-height: 56px;
		}
		.efpic-ad-slot--addons-page {
			min-height: 320px;
		}
		.efpic-ad-slot--welcome-sidebar {
			min-height: 140px;
		}
		.efpic-ad-slot--multi-client-hint {
			min-height: 32px;
			margin: 6px 0 0;
		}
	</style>
	<?php
}

add_action( 'admin_head', 'efpic_ad_slots_admin_styles' );
