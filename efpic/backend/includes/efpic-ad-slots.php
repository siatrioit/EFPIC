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
 * Render an admin content slot only when a filter provides HTML.
 *
 * Empty reserved boxes are no longer printed (they looked like broken UI).
 *
 * @param string $slot_id       Unique slot identifier (used in CSS class).
 * @param string $wrapper_class Optional extra wrapper classes (legacy layout hooks).
 */
function efpic_render_ad_slot( $slot_id, $wrapper_class = '' ) {
	/**
	 * Filter optional HTML for an admin ad/content slot.
	 *
	 * @param string $content Slot HTML (empty = render nothing).
	 * @param string $slot_id Slot id.
	 */
	$content = apply_filters( 'efpic_ad_slot_content', '', $slot_id );
	if ( '' === (string) $content ) {
		return;
	}

	$classes = array(
		'efpic-ad-slot',
		'efpic-ad-slot--' . sanitize_html_class( $slot_id ),
	);

	if ( ! empty( $wrapper_class ) ) {
		$classes[] = $wrapper_class;
	}

	printf(
		'<div class="%1$s" data-efpic-ad-slot="%2$s">%3$s</div>',
		esc_attr( implode( ' ', $classes ) ),
		esc_attr( $slot_id ),
		$content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered HTML
	);
}
