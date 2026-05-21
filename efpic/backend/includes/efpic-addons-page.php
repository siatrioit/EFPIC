<?php
/**
 * efpic Pro admin page — promotional content removed; reserved layout slots.
 *
 * @since 0.7.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the efpic Pro submenu page with empty custom content areas.
 */
function efpic_load_add_ons_page() {
	?>
	<div class="efpic-pro__head-wrapper">
		<?php efpic_render_ad_slot( 'addons-header', 'efpic-pro__head-line' ); ?>
	</div>
	<div class="wrap pro-page__wrap">
		<h2 style="display: none;"></h2>
		<div class="pro-page__wrap-inner">
			<?php efpic_render_ad_slot( 'addons-hero', 'pro-page__header' ); ?>
		</div>
		<?php efpic_render_ad_slot( 'addons-comparison', 'pro-page__comparison' ); ?>
		<?php efpic_render_ad_slot( 'addons-features', 'pro-page__features' ); ?>
		<?php efpic_render_ad_slot( 'addons-social-proof', 'pro-page__social-proof' ); ?>
		<?php efpic_render_ad_slot( 'addons-pricing', 'efpic-pro__pricing-section' ); ?>
		<?php efpic_render_ad_slot( 'addons-page', 'efpic-ad-slot-page' ); ?>
	</div>
	<?php
}

add_filter( 'option_page_capability_efpic_addon_licenses', 'efpic_capability' );
